<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ShopsId;

/**
 * Страница «Магазины» (п.2 ТЗ): единая интерактивная карта + панель списка.
 * Макет Figma: секция 758:168 (десктоп 789:20251/784:8760, мобайл 786:17088…,
 * модалка геолокации 789:20645).
 */
class ShopsController extends Controller
{
    public function index()
    {
        $view = 'front.pages.shops.list';

        $segment_2 = request()->segment(2);
        $menu_id = getItemByAlias($segment_2, 'MenuId');

        $shops = ShopsId::where('active', 1)
            ->has('itemByLang')
            ->with('itemByLang', 'moduleMultipleImg')
            ->orderBy('position', 'asc')
            ->get();

        // плоские данные для карты и списка; город — первый сегмент названия
        // («Кишинев, Рышкановка»), привязка city_id в базе не заполнена
        $shops_data = $shops->map(function ($one_shop) {
            $name = $one_shop->itemByLang->name ?? '';
            $parts = array_map(fn ($part) => trim($part, " :\t"), explode(',', $name));

            $images = ($one_shop->moduleMultipleImg ?? collect())
                ->filter(fn ($img) => $img->img && file_exists(public_path('upfiles/shops/' . $img->img)))
                ->map(fn ($img) => asset('upfiles/shops/' . $img->img))
                ->values();

            return [
                'id' => $one_shop->id,
                'name' => trim($name, " :\t"),
                'label' => count($parts) > 1 ? implode(', ', array_slice($parts, 1)) : $parts[0],
                'city' => $parts[0],
                'address' => $one_shop->itemByLang->address ?? '',
                'phone' => $one_shop->phone ?? '',
                'schedule' => $one_shop->itemByLang->schedule ?? '',
                'lat' => (float) $one_shop->latitude ?: null,
                'lng' => (float) $one_shop->longitude ?: null,
                'images' => $images,
            ];
        })->filter(fn ($one) => $one['lat'] && $one['lng'])->values();

        // города: Кишинёв первым, дальше по алфавиту (решение тикета о сортировке)
        $shops_cities = $shops_data->pluck('city')->unique()
            ->sort(function ($a, $b) {
                $a_main = mb_stripos($a, 'Кишин') !== false || mb_stripos($a, 'Chi') === 0;
                $b_main = mb_stripos($b, 'Кишин') !== false || mb_stripos($b, 'Chi') === 0;

                if ($a_main !== $b_main) {
                    return $a_main ? -1 : 1;
                }

                return mb_strtolower($a) <=> mb_strtolower($b);
            })
            ->values();

        $google_maps_key = config('custom.front.google_maps_key');

        $meta = $menu_id ?? collect([]);

        return view($view, get_defined_vars());
    }
}
