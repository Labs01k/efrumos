<?php

namespace App\Http\Middleware\AppApi;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Авторизация сервис-к-сервису для /api/app/v1/*: бэкенд приложения
 * лояльности (eFrumos-backend) приходит с `Authorization: Bearer <APP_API_TOKEN>`.
 * Пользователя приложения этот токен не идентифицирует — клиента передаёт
 * само приложение заголовком X-App-Client-Id (оно уже проверило его по OTP).
 *
 * Формат ошибок совпадает с их middleware Verify1CWebhookToken:
 * {"success": false, "message": "..."}.
 */
class AuthenticateAppService
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.app_api.token', '');

        if ($expected === '') {
            return response()->json([
                'success' => false,
                'message' => 'App API token is not configured.',
            ], 503);
        }

        if (!hash_equals($expected, (string) $request->bearerToken())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
