<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\FrontUser;
use App\Services\Integration\EfrumosBackend\EfrumosBackendClient;
use App\Services\Integration\EfrumosBackend\EfrumosBackendException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * EFA-39 / E1-BES-01 — вход и регистрация на сайте по телефону, для
 * посетителя, который ЕЩЁ НЕ вошёл (в отличие от PhoneConfirmationController,
 * E1-BES-02 — там телефон подтверждает уже залогиненный пользователь).
 * OTP идёт через eFrumos-backend (тот же общий сервис, E1-BE-07), сайт сам
 * SMS не шлёт. Email/Google/Facebook не трогаются, работают как раньше —
 * это НОВЫЙ, третий способ входа, не замена существующих.
 */
class PhoneAuthController extends Controller
{
    private const PHONE_MD_E164_REGEX = '/^\+373\d{8}$/';

    public function __construct(private readonly EfrumosBackendClient $backendClient)
    {
    }

    public function ajaxSendLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:' . self::PHONE_MD_E164_REGEX],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first('phone')]);
        }

        try {
            $result = $this->backendClient->sendOtp($request->input('phone'));
        } catch (EfrumosBackendException $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }

        return response()->json([
            'status' => true,
            'expires_at' => $result['expires_at'] ?? null,
            'cooldown_seconds' => $result['cooldown_seconds'] ?? 60,
        ]);
    }

    public function ajaxConfirmLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:' . self::PHONE_MD_E164_REGEX],
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $phone = $request->input('phone');
        $code = $request->input('code');

        try {
            $this->backendClient->confirmOtp($phone, $code);
        } catch (EfrumosBackendException $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }

        // Телефон уже подтверждался на другом аккаунте — входим в НЕГО, а не
        // создаём второй. Только whereNotNull(phone_confirmed_at): сырое
        // совпадение с чьим-то НЕподтверждённым phone (например, введённым
        // один раз при гостевом заказе) не в счёт — это не доказывает, что
        // номер принадлежит тому аккаунту.
        $user = FrontUser::where('phone', $phone)->whereNotNull('phone_confirmed_at')->first();

        if (!$user) {
            try {
                $loyaltyClient = $this->backendClient->resolveLoyaltyClient([
                    'phone' => $phone,
                    'name' => 'Client',
                ]);
            } catch (EfrumosBackendException $exception) {
                return response()->json(['status' => false, 'message' => $exception->getMessage()]);
            }

            $user = new FrontUser();
            $user->phone = $phone;
            $user->phone_confirmed_at = now();
            $user->app_loyalty_client_id = $loyaltyClient['client_id'];
            $user->active = 1;
            $user->confirmation = 1;
            $user->save();
        }

        Session::put('session-front-user', $user->id);

        return response()->json([
            'status' => true,
            'is_new' => $user->wasRecentlyCreated,
            'redirect' => $request->input('current_url'),
        ]);
    }
}
