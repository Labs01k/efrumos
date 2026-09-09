<?php

namespace App\Services\GooglePlaces;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Epic 2 — часы работы, статус «Открыт/Закрыт» и телефон магазина по
 * Google Place ID. Один и тот же ключ, что и у карты/CMS-виджета координат
 * (config('custom.front.google_maps_key'), консолидировано 2026-09-09 —
 * раньше было 2 отдельные переменные с одним и тем же значением).
 *
 * Живьём проверено 2026-09-09: реальный place_id ("Efrumos Beauty Ciocana")
 * вернул настоящие opening_hours/business_status/formatted_phone_number —
 * ключ и Places API рабочие, биллинг подтверждён.
 *
 * Короткий кеш (15 минут), а не «на каждый рендер страницы» — Place Details
 * не бесплатный вызов, а часы работы магазина не меняются внутри дня.
 */
class GooglePlacesService
{
    private const CACHE_TTL_SECONDS = 900;

    /**
     * @return array{open_now: ?bool, weekday_text: array<string>, phone: ?string, business_status: ?string}|null
     *         null — ключ не настроен или Google вернул ошибку (плохой/устаревший place_id и т.п.)
     */
    public function getDetails(string $placeId): ?array
    {
        $key = config('custom.front.google_maps_key');

        if (!$key || !$placeId) {
            return null;
        }

        return Cache::remember(
            "google_places_details:{$placeId}",
            self::CACHE_TTL_SECONDS,
            function () use ($placeId, $key) {
                try {
                    $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                        'place_id' => $placeId,
                        'fields' => 'opening_hours,formatted_phone_number,business_status',
                        'key' => $key,
                    ]);

                    $data = $response->json();

                    if (($data['status'] ?? null) !== 'OK') {
                        Log::warning('GooglePlacesService: Place Details вернул не-OK статус', [
                            'place_id' => $placeId,
                            'status' => $data['status'] ?? null,
                            'error_message' => $data['error_message'] ?? null,
                        ]);

                        return null;
                    }

                    $result = $data['result'] ?? [];

                    return [
                        'open_now' => $result['opening_hours']['open_now'] ?? null,
                        'weekday_text' => $result['opening_hours']['weekday_text'] ?? [],
                        'phone' => $result['formatted_phone_number'] ?? null,
                        'business_status' => $result['business_status'] ?? null,
                    ];
                } catch (\Throwable $e) {
                    Log::warning('GooglePlacesService: запрос к Place Details упал', [
                        'place_id' => $placeId,
                        'error' => $e->getMessage(),
                    ]);

                    return null;
                }
            }
        );
    }
}
