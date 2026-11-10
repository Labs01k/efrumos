<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\FrontUser;
use App\Services\AccountLink\AccountLinkRedeemException;
use App\Services\AccountLink\AccountLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * EFA-35 / E1-BE-04 — бэкенд приложения погашает одноразовый токен,
 * выданный сайтом после логина на странице режима связки (EFA-43 /
 * AccountLinkController::buildAppRedirectUrl). Погашение одноразовое —
 * повторный вызов с тем же токеном получит 409. Этот эндпоинт сам НЕ
 * проверяет, кто его вызвал (нет сессии приложения) — это умышленно
 * оставлено бэкенду приложения (EFA-35, auth:sanctum там), а здесь
 * единственная защита от перехвата токена — PKCE (code_verifier).
 */
class AccountLinkController extends Controller
{
    public function redeem(Request $request, AccountLinkService $service): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:64',
            'code_verifier' => 'nullable|string|max:255',
            'app_client_id' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $frontUser = $service->redeem(
                (string) $request->input('token'),
                $request->input('code_verifier') !== null ? (string) $request->input('code_verifier') : null,
            );

            // EFA-77 — известный пробел, закрыт 10.11.2026: пользователь,
            // связавшийся через EFA-43/35 без подтверждения телефона на
            // сайте, до этого не имел app_loyalty_client_id вообще — а
            // избранное/каталог приложения резолвят клиента именно по нему
            // (X-App-Client-Id), не по site_front_user_id. Пишем сюда карту
            // лояльности приложения прямо при связке — это и есть мост
            // между двумя идентичностями.
            $appClientId = $request->input('app_client_id') !== null ? (string) $request->input('app_client_id') : null;
            if ($appClientId !== null && $frontUser->app_loyalty_client_id !== $appClientId) {
                $claimedByOther = FrontUser::where('app_loyalty_client_id', $appClientId)
                    ->where('id', '!=', $frontUser->id)
                    ->exists();

                if ($claimedByOther) {
                    Log::warning('AccountLink: redeem — app_loyalty_client_id уже привязан к другому front_user', [
                        'front_user_id' => $frontUser->id,
                        'app_client_id' => $appClientId,
                    ]);

                    throw new AccountLinkRedeemException(
                        'app_loyalty_client_id уже привязан к другому аккаунту сайта.',
                        AccountLinkRedeemException::LOYALTY_CLIENT_TAKEN,
                    );
                }

                Log::info('AccountLink: app_loyalty_client_id привязан к front_user (EFA-77)', [
                    'front_user_id' => $frontUser->id,
                    'app_client_id' => $appClientId,
                    'previous_value' => $frontUser->app_loyalty_client_id,
                ]);

                $frontUser->update(['app_loyalty_client_id' => $appClientId]);
            }
        } catch (AccountLinkRedeemException $e) {
            // Сама причина уже залогирована там, где брошено исключение
            // (AccountLinkService::redeem() для invalid/used/expired/
            // pkce_mismatch, выше в этом методе для loyalty_client_taken) —
            // здесь второй раз не логируем, чтобы не дублировать.
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'reason' => $e->reason()], 409);
        }

        Log::info('AccountLink: redeem успешен', [
            'front_user_id' => $frontUser->id,
            'app_client_id_bridged' => $appClientId !== null,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'front_user_id' => $frontUser->id,
                'name' => $frontUser->name,
                'last_name' => $frontUser->last_name,
                'email' => $frontUser->email,
                'phone' => $frontUser->phone,
                'phone_confirmed' => $frontUser->phone_confirmed_at !== null,
                'app_loyalty_client_id' => $frontUser->app_loyalty_client_id,
            ],
        ]);
    }
}
