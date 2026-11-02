<?php

namespace App\Services\AccountLink;

use App\Models\AccountLinkToken;
use App\Models\FrontUser;
use RuntimeException;

/**
 * EFA-43 / E1-BES-05 — одноразовое подтверждение «этот front_user только что
 * вошёл на сайте», возвращаемое приложению после логина в режиме связки.
 * Погашается бэкендом приложения один раз (EFA-35) через AppApi-эндпоинт,
 * использующий redeem() ниже.
 */
class AccountLinkService
{
    public function issue(FrontUser $user, ?string $appState): AccountLinkToken
    {
        return AccountLinkToken::create([
            'token' => bin2hex(random_bytes(32)),
            'front_user_id' => $user->id,
            'app_state' => $appState !== null ? mb_substr($appState, 0, 255) : null,
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
     * Погашает токен ровно один раз. Бросает RuntimeException с понятной
     * причиной для невалидного/использованного/просроченного токена —
     * вызывающий код (AppApi-контроллер) сам решает, каким HTTP-кодом это
     * обернуть.
     */
    public function redeem(string $token): FrontUser
    {
        $linkToken = AccountLinkToken::where('token', $token)->first();

        if (!$linkToken) {
            throw new RuntimeException('Токен не найден.');
        }

        if ($linkToken->used_at !== null) {
            throw new RuntimeException('Токен уже использован.');
        }

        if ($linkToken->expires_at->isPast()) {
            throw new RuntimeException('Токен истёк.');
        }

        $linkToken->update(['used_at' => now()]);

        return $linkToken->frontUser;
    }
}
