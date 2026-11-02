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

        // Уже вошёл на сайт в этой же сессии (например, повторно открыл
        // WebView) — не просим логиниться второй раз, сразу выдаём токен.
        // Берём state из ТЕКУЩЕГО запроса, а не из сессии — свежий логин
        // (ajaxLoginUser/Social-callback) уже стёр account_link.state после
        // использования, так что к этому моменту там могло остаться пусто.
        $frontUserId = Session::get('session-front-user');

        if ($frontUserId) {
            $frontUser = FrontUser::where('id', $frontUserId)->where('active', 1)->first();

            if ($frontUser) {
                return redirect(static::buildAppRedirectUrl($frontUser, $state !== '' ? $state : null));
            }
        }

        Session::put('account_link.active', true);
        Session::put('account_link.state', $state);

        return view('front.account-link.login');
    }

    public static function buildAppRedirectUrl(FrontUser $frontUser, ?string $state = null): string
    {
        $state = $state ?? Session::get('account_link.state');
        $linkToken = app(AccountLinkService::class)->issue($frontUser, $state !== null && $state !== '' ? $state : null);

        Session::forget(['account_link.active', 'account_link.state']);

        return app(AccountLinkService::class)->buildReturnUrl($linkToken);
    }
}
