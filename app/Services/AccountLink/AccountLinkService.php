<?php

namespace App\Services\AccountLink;

use App\Models\AccountLinkToken;
use App\Models\FrontUser;
use Illuminate\Support\Facades\Log;

/**
 * EFA-43 / E1-BES-05 — одноразовое подтверждение «этот front_user только что
 * вошёл на сайте», возвращаемое приложению после логина в режиме связки.
 * Погашается бэкендом приложения один раз (EFA-35) через AppApi-эндпоинт,
 * использующий redeem() ниже.
 */
class AccountLinkService
{
    public function issue(FrontUser $user, ?string $appState, ?string $codeChallenge = null): AccountLinkToken
    {
        $linkToken = AccountLinkToken::create([
            'token' => bin2hex(random_bytes(32)),
            'front_user_id' => $user->id,
            'app_state' => $appState !== null ? mb_substr($appState, 0, 255) : null,
            // PKCE (RFC 8252) — efrumosapp:// может перехватить другое
            // приложение на Android, одноразовости и TTL токена не хватает
            // (перехватчик использует токен раньше владельца). Приложение
            // само генерирует code_verifier, сюда приходит только его хэш —
            // сверяем на redeem(), сам verifier сайт никогда не видит.
            'code_challenge' => $codeChallenge !== null ? mb_substr($codeChallenge, 0, 64) : null,
            'expires_at' => now()->addMinutes((int) config('services.account_link.token_ttl_minutes', 10)),
        ]);

        // Сам токен не логируем — одноразовый секрет, не отличается по
        // риску от пароля. token_id достаточно, чтобы найти строку в БД
        // при отладке.
        Log::info('AccountLink: токен выдан', [
            'token_id' => $linkToken->id,
            'front_user_id' => $user->id,
            'has_pkce' => $linkToken->code_challenge !== null,
        ]);

        return $linkToken;
    }

    public function buildReturnUrl(AccountLinkToken $linkToken): string
    {
        $base = (string) config('services.account_link.app_return_url');

        $query = ['token' => $linkToken->token];
        if ($linkToken->app_state !== null) {
            $query['state'] = $linkToken->app_state;
        }

        return $base . (str_contains($base, '?') ? '&' : '?') . http_build_query($query);
    }

    /**
     * Deep-link на приложение с кодом ошибки вместо токена — для случая,
     * когда сайт не смог выдать токен (см. AccountLinkController::buildAppRedirectUrl).
     * У приложения просто нет другого способа узнать, что что-то пошло не так.
     */
    public function buildErrorReturnUrl(string $error, ?string $appState): string
    {
        $base = (string) config('services.account_link.app_return_url');

        $query = ['error' => $error];
        if ($appState !== null && $appState !== '') {
            $query['state'] = $appState;
        }

        return $base . (str_contains($base, '?') ? '&' : '?') . http_build_query($query);
    }

    /**
     * Погашает токен ровно один раз. Бросает AccountLinkRedeemException с
     * машиночитаемой причиной (invalid/used/expired/pkce_mismatch) —
     * вызывающий код (AppApi-контроллер) решает, каким HTTP-кодом и текстом
     * это обернуть для приложения.
     */
    public function redeem(string $token, ?string $codeVerifier = null): FrontUser
    {
        $linkToken = AccountLinkToken::where('token', $token)->first();

        if (!$linkToken) {
            // Токена с таким значением вообще нет — логируем короткий
            // префикс самого значения (не весь токен), этого достаточно,
            // чтобы отличить "опечатка/старое значение" от явного перебора.
            Log::warning('AccountLink: redeem — токен не найден', ['token_prefix' => mb_substr($token, 0, 8)]);

            throw new AccountLinkRedeemException('Токен не найден.', AccountLinkRedeemException::INVALID);
        }

        if ($linkToken->used_at !== null) {
            Log::warning('AccountLink: redeem — токен уже использован', ['token_id' => $linkToken->id, 'used_at' => $linkToken->used_at]);

            throw new AccountLinkRedeemException('Токен уже использован.', AccountLinkRedeemException::USED);
        }

        if ($linkToken->expires_at->isPast()) {
            Log::warning('AccountLink: redeem — токен истёк', ['token_id' => $linkToken->id, 'expires_at' => $linkToken->expires_at]);

            throw new AccountLinkRedeemException('Токен истёк.', AccountLinkRedeemException::EXPIRED);
        }

        // Токен выдан с code_challenge — значит, перехватчик без самого
        // code_verifier (который сайт никогда не видел и не хранит) не
        // сможет его погасить, даже зная сам токен.
        if ($linkToken->code_challenge !== null) {
            $verifierHash = $codeVerifier !== null ? hash('sha256', $codeVerifier) : null;

            if ($verifierHash === null || !hash_equals($linkToken->code_challenge, $verifierHash)) {
                // Живой симптом перехвата токена, если это реально
                // повторится в проде без видимой причины — стоит
                // насторожиться, не просто отладочный шум.
                Log::warning('AccountLink: redeem — code_verifier не совпадает (возможная попытка перехвата токена)', [
                    'token_id' => $linkToken->id,
                    'front_user_id' => $linkToken->front_user_id,
                    'verifier_provided' => $codeVerifier !== null,
                ]);

                throw new AccountLinkRedeemException('code_verifier не совпадает.', AccountLinkRedeemException::PKCE_MISMATCH);
            }
        }

        $linkToken->update(['used_at' => now()]);

        Log::info('AccountLink: токен погашен', ['token_id' => $linkToken->id, 'front_user_id' => $linkToken->front_user_id]);

        return $linkToken->frontUser;
    }
}
