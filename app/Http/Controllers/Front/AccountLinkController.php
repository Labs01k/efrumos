<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\FrontUser;
use App\Services\AccountLink\AccountLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * EFA-43 / E1-BES-05 — страница входа в режиме связки, открывается
 * приложением внутри WebView. Логин (email/Google/Facebook) — тот же самый,
 * что и на обычном сайте (UserAuthController::ajaxLoginUser,
 * SocialAuthController), эти два контроллера сами проверяют
 * `account_link.active` в сессии и после успешного входа уводят на deep-link
 * приложения с одноразовым токеном (buildAppRedirectUrl() ниже) вместо
 * обычного редиректа на главную/current_url.
 */
class AccountLinkController extends Controller
{
    public function loginPage(Request $request)
    {
        $state = mb_substr((string) $request->query('state', ''), 0, 255);
        // PKCE (RFC 8252) — хэш code_verifier, который приложение сгенерировало
        // у себя. Сайт его только хранит рядом с токеном, сам verifier никогда
        // не видит — см. AccountLinkService::redeem().
        $codeChallenge = mb_substr((string) $request->query('code_challenge', ''), 0, 64);

        // Уже вошёл на сайт в этой же сессии (например, повторно открыл
        // системный браузер) — не просим логиниться второй раз, сразу выдаём
        // токен. Берём state/code_challenge из ТЕКУЩЕГО запроса, а не из
        // сессии — свежий логин (ajaxLoginUser/Social-callback) уже стёр
        // account_link.state после использования, так что к этому моменту
        // там могло остаться пусто.
        $frontUserId = Session::get('session-front-user');

        if ($frontUserId) {
            $frontUser = FrontUser::where('id', $frontUserId)->where('active', 1)->first();

            if ($frontUser) {
                return redirect(static::buildAppRedirectUrl(
                    $frontUser,
                    $state !== '' ? $state : null,
                    $codeChallenge !== '' ? $codeChallenge : null,
                ));
            }
        }

        Session::put('account_link.active', true);
        Session::put('account_link.state', $state);
        Session::put('account_link.code_challenge', $codeChallenge);

        return view('front.account-link.login');
    }

    /**
     * На любую ошибку выдачи токена — redirect на deep-link приложения с
     * `error=`, а не 500: пользователь уже внутри системного окна браузера,
     * ему больше некуда вернуться самому.
     */
    public static function buildAppRedirectUrl(FrontUser $frontUser, ?string $state = null, ?string $codeChallenge = null): string
    {
        $state = $state ?? Session::get('account_link.state');
        $codeChallenge = $codeChallenge ?? Session::get('account_link.code_challenge');
        Session::forget(['account_link.active', 'account_link.state', 'account_link.code_challenge']);

        $service = app(AccountLinkService::class);

        try {
            $linkToken = $service->issue(
                $frontUser,
                $state !== null && $state !== '' ? $state : null,
                $codeChallenge !== null && $codeChallenge !== '' ? $codeChallenge : null,
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AccountLink: не удалось выдать токен связки', [
                'front_user_id' => $frontUser->id,
                'error' => $e->getMessage(),
            ]);

            return $service->buildErrorReturnUrl('link_failed', $state);
        }

        return $service->buildReturnUrl($linkToken);
    }
}
