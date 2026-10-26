<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\FrontUser;
use App\Services\Integration\EfrumosBackend\EfrumosBackendClient;
use App\Services\Integration\EfrumosBackend\EfrumosBackendException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * E1-BES-02 — подтверждение телефона и привязка карты лояльности для
 * существующего аккаунта сайта (личный кабинет). OTP и резолв карты идут
 * через eFrumos-backend (EfrumosBackendClient) — общий сервис с
 * приложением, сайт сам SMS не шлёт (E1-BE-07).
 *
 * E1-BES-03 — правило показа поп-апа подтверждения (раз в 7 дней после
 * «Позже», ТЗ §4 М1 / xlsx): ajaxShouldShowPhoneConfirmPopup/
 * ajaxDismissPhoneConfirmPopup ниже. Баннер в ЛК отдельного эндпоинта не
 * требует — «постоянно до подтверждения» выводится по уже отдаваемому
 * профилю через phone_confirmed_at.
 *
 * E1-BES-05 (режим входа для СВЯЗКИ из приложения через WebView) и
 * E1-BES-01 (вход/регистрация НОВОГО пользователя по телефону, без
 * предшествующего email-аккаунта) — отдельные, более крупные задачи,
 * сюда не входят.
 */
class PhoneConfirmationController extends Controller
{
    private const PHONE_MD_E164_REGEX = '/^\+373\d{8}$/';

    private const POPUP_COOLDOWN_DAYS = 7;

    public function __construct(private readonly EfrumosBackendClient $backendClient)
    {
    }

    public function ajaxSendPhoneOtp(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json(['status' => false, 'message' => ShowLabelById(62)]);
        }

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:' . self::PHONE_MD_E164_REGEX],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first('phone')]);
        }

        $phone = $request->input('phone');

        // БП-1/E1-BE-03: один подтверждённый телефон — один профиль.
        // Проверяем здесь, до похода в backend, чтобы не тратить SMS-лимит
        // впустую на заведомо занятый номер.
        $taken = FrontUser::where('phone', $phone)
            ->whereNotNull('phone_confirmed_at')
            ->where('id', '!=', $user->id)
            ->exists();

        if ($taken) {
            return response()->json([
                'status' => false,
                'message' => 'Этот номер телефона уже привязан к другому аккаунту.',
            ]);
        }

        try {
            $result = $this->backendClient->sendOtp($phone);
        } catch (EfrumosBackendException $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }

        return response()->json([
            'status' => true,
            'expires_at' => $result['expires_at'] ?? null,
            'cooldown_seconds' => $result['cooldown_seconds'] ?? 60,
        ]);
    }

    public function ajaxConfirmPhoneOtp(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json(['status' => false, 'message' => ShowLabelById(62)]);
        }

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:' . self::PHONE_MD_E164_REGEX],
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $phone = $request->input('phone');
        $code = $request->input('code');

        $taken = FrontUser::where('phone', $phone)
            ->whereNotNull('phone_confirmed_at')
            ->where('id', '!=', $user->id)
            ->exists();

        if ($taken) {
            return response()->json([
                'status' => false,
                'message' => 'Этот номер телефона уже привязан к другому аккаунту.',
            ]);
        }

        try {
            $this->backendClient->confirmOtp($phone, $code);

            $loyaltyClient = $this->backendClient->resolveLoyaltyClient([
                'phone' => $phone,
                'name' => $user->name ?: 'Client',
                'surname' => $user->last_name,
                'date_of_birth' => $user->birth,
                'sex' => $this->normalizeSex($user->gender),
            ]);
        } catch (EfrumosBackendException $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }

        $user->update([
            'phone' => $phone,
            'phone_confirmed_at' => now(),
            'app_loyalty_client_id' => $loyaltyClient['client_id'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Телефон подтверждён, карта лояльности привязана.',
        ]);
    }

    /**
     * E1-BES-03 — вызывается FE после входа. true — пора снова показать
     * поп-ап: телефон не подтверждён, и либо его никогда не отклоняли, либо
     * последний «Позже» был 7+ дней назад.
     */
    public function ajaxShouldShowPhoneConfirmPopup(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json(['status' => false, 'message' => ShowLabelById(62)]);
        }

        if ($user->phone_confirmed_at !== null) {
            return response()->json(['status' => true, 'show_popup' => false]);
        }

        $dismissedAt = $user->phone_confirm_popup_dismissed_at;
        $show = $dismissedAt === null || $dismissedAt->lte(now()->subDays(self::POPUP_COOLDOWN_DAYS));

        return response()->json(['status' => true, 'show_popup' => $show]);
    }

    /** E1-BES-03 — вызывается FE, когда пользователь нажал «Позже». */
    public function ajaxDismissPhoneConfirmPopup(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json(['status' => false, 'message' => ShowLabelById(62)]);
        }

        $user->update(['phone_confirm_popup_dismissed_at' => now()]);

        return response()->json(['status' => true]);
    }

    /** FrontUser.gender формат сайта может отличаться от male/female 1С — свести здесь, не размазывать по вызывающему коду. */
    private function normalizeSex(?string $gender): ?string
    {
        return match (true) {
            $gender === null => null,
            str_starts_with(mb_strtolower($gender), 'm') => 'male',
            str_starts_with(mb_strtolower($gender), 'f') => 'female',
            default => null,
        };
    }
}
