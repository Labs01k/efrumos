<?php

namespace App\Services\AccountLink;

use App\Models\AccountLinkToken;
use App\Models\FrontUser;

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
        return AccountLinkToken::create([
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
            throw new AccountLinkRedeemException('Токен не найден.', AccountLinkRedeemException::INVALID);
        }

        if ($linkToken->used_at !== null) {
            throw new AccountLinkRedeemException('Токен уже использован.', AccountLinkRedeemException::USED);
        }

        if ($linkToken->expires_at->isPast()) {
            throw new AccountLinkRedeemException('Токен истёк.', AccountLinkRedeemException::EXPIRED);
        }

        // Токен выдан с code_challenge — значит, перехватчик без самого
        // code_verifier (который сайт никогда не видел и не хранит) не
        // сможет его погасить, даже зная сам токен.
        if ($linkToken->code_challenge !== null) {
            $verifierHash = $codeVerifier !== null ? hash('sha256', $codeVerifier) : null;

            if ($verifierHash === null || !hash_equals($linkToken->code_challenge, $verifierHash)) {
                throw new AccountLinkRedeemException('code_verifier не совпадает.', AccountLinkRedeemException::PKCE_MISMATCH);
            }
        }

        $linkToken->update(['used_at' => now()]);

        return $linkToken->frontUser;
    }
}
