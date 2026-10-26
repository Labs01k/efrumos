<?php

namespace App\Services\Integration\EfrumosBackend;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Сайт как service-to-service клиент к eFrumos-backend (E1 — общий
 * OTP-сервис для входа по телефону + резолв карты 1С), зеркало того, как
 * бэкенд ходит к сайту (SiteApiClient на его стороне). Отдельный токен от
 * app_api.status_webhook_token — тот для событий, этот для синхронных
 * вызовов в реальном времени.
 */
class EfrumosBackendClient
{
    /** @return array{message:string,expires_at:string,cooldown_seconds:int} */
    public function sendOtp(string $phone): array
    {
        return $this->post('site/phone/send-otp', ['phone' => $phone]);
    }

    /** @return array{verified:bool} */
    public function confirmOtp(string $phone, string $code): array
    {
        return $this->post('site/phone/confirm-otp', ['phone' => $phone, 'code' => $code]);
    }

    /**
     * @param array{phone:string,name:string,surname?:string|null,patronymic?:string|null,date_of_birth?:string|null,sex?:string|null} $payload
     * @return array{client_id:string,barcode_value:string}
     */
    public function resolveLoyaltyClient(array $payload): array
    {
        return $this->post('site/phone/resolve-loyalty-client', $payload);
    }

    private function post(string $path, array $payload): array
    {
        $baseUrl = (string) config('services.app_api.backend_base_url', '');
        $token = (string) config('services.app_api.backend_service_token', '');

        if ($baseUrl === '' || $token === '') {
            throw new EfrumosBackendException('eFrumos-backend is not configured.', 503);
        }

        $url = rtrim($baseUrl, '/').'/api/'.$path;
        $timeout = (int) config('services.app_api.backend_timeout', 10);

        try {
            $response = Http::timeout($timeout)
                ->withToken($token)
                ->acceptJson()
                ->post($url, $payload);
        } catch (RequestException|Throwable $exception) {
            Log::warning('[EfrumosBackendClient] request failed', ['url' => $url, 'error' => $exception->getMessage()]);

            throw new EfrumosBackendException('eFrumos-backend is unavailable.', 503, $exception);
        }

        $data = $response->json();

        if ($response->failed()) {
            $message = is_array($data) && is_string($data['message'] ?? null)
                ? $data['message']
                : 'eFrumos-backend rejected the request.';

            throw new EfrumosBackendException($message, $response->status());
        }

        if (! is_array($data)) {
            throw new EfrumosBackendException('Unexpected eFrumos-backend response format.', 503);
        }

        return $data;
    }
}
