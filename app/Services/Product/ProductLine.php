<?php

namespace App\Services\Product;

use Illuminate\Support\Collection;

/**
 * Линейка краски как единица каталога (ответ заказчика 1.1 от 07.09.2026:
 * «одна карточка на линейку»).
 *
 * В 1С каждый оттенок — отдельный SKU, и на сайте это отдельный товар со своим
 * адресом. В списке каталога такая линейка занимала до 125 почти одинаковых
 * позиций, поэтому в выдаче она схлопывается в одну карточку, а выбор оттенка
 * происходит уже на странице товара, в палитре.
 *
 * Ключ линейки — пара (brand_id, goods_type_id), а не один brand_id:
 * под одним brand_id лежат разные типы товара (у «Artisto Color» это
 * перманентная краска, краска для бровей, обесцвечивающая и корректор),
 * а ещё на brand_id красок висят наборы, оксигенты и шампуни.
 * Тот же ключ использует ShadePalette — каталог и палитра обязаны
 * группировать одинаково, иначе карточка обещает N оттенков,
 * а палитра показывает другой список.
 */
class ProductLine
{
    /**
     * Ключ линейки или null, если товар схлопывать нельзя.
     * Работает и с моделью, и с лёгкой строкой выборки каталога.
     */
    public static function key($goods_item): ?string
    {
        $goods_type_id = (int) ($goods_item->goods_type_id ?? 0);
        $brand_id = (int) ($goods_item->brand_id ?? 0);

        if ($brand_id <= 0 || !self::isDyeType($goods_type_id)) {
            return null;
        }

        return $brand_id . '|' . $goods_type_id;
    }

    /** Краска ли это — список типов товара задан в конфиге. */
    public static function isDyeType(int $goods_type_id): bool
    {
        $dye_type_ids = array_map('intval', config('custom.front.dye_goods_type_ids', []));

        return in_array($goods_type_id, $dye_type_ids, true);
    }

    /**
     * Схлопывает упорядоченный список товаров в список представителей.
     *
     * Представитель — ПЕРВЫЙ товар линейки в текущем порядке выдачи. Это даёт
     * бесплатно всё нужное: при сортировке по цене линейка встаёт в выдачу по
     * своей минимальной (или максимальной) цене, при фильтре — представитель
     * заведомо удовлетворяет фильтру, а порядок детерминирован, потому что
     * запрос заканчивается тай-брейком по id.
     *
     * Возвращает [id представителей в прежнем порядке, метаданные по id].
     */
    public static function collapse(Collection $rows): array
    {
        $representatives = [];
        $lines = [];

        foreach ($rows as $one_row) {
            $key = self::key($one_row);

            // не-краски и краски без линейки идут в выдачу поштучно, как раньше
            if ($key === null) {
                $representatives[] = (int) $one_row->id;
                continue;
            }

            $price = self::effectivePrice($one_row);

            if (!isset($lines[$key])) {
                $representatives[] = (int) $one_row->id;

                $lines[$key] = [
                    'representative_id' => (int) $one_row->id,
                    'shades_count' => 0,
                    'price_min' => $price,
                    'price_max' => $price,
                    'promo_count' => 0,
                ];
            }

            $lines[$key]['shades_count']++;
            $lines[$key]['price_min'] = min($lines[$key]['price_min'], $price);
            $lines[$key]['price_max'] = max($lines[$key]['price_max'], $price);

            if ($one_row->price_promo > 0) {
                $lines[$key]['promo_count']++;
            }
        }

        // одна линейка может дать в выдаче несколько карточек: под brand_id
        // «Princess Essex» лежат и краска, и корректор, и обесцвечивающая.
        // Тогда к названию линейки добавляется тип товара, иначе в выдаче
        // окажутся три карточки с одинаковым заголовком.
        $cards_per_brand = [];

        foreach (array_keys($lines) as $one_key) {
            $brand_id = explode('|', $one_key)[0];
            $cards_per_brand[$brand_id] = ($cards_per_brand[$brand_id] ?? 0) + 1;
        }

        $meta = [];

        foreach ($lines as $one_key => $one_line) {
            // линейка из одного товара — обычная карточка товара, не линейки
            if ($one_line['shades_count'] < 2) {
                continue;
            }

            $meta[$one_line['representative_id']] = [
                'line_shades_count' => $one_line['shades_count'],
                'line_price_min' => $one_line['price_min'],
                'line_price_max' => $one_line['price_max'],
                'line_show_type' => ($cards_per_brand[explode('|', $one_key)[0]] ?? 1) > 1,
                // бейдж «Акция» на карточке линейки честен, только если акция
                // распространяется на все оттенки: у Artisto Color промо
                // висит на 92 оттенках из 105
                'line_all_promo' => $one_line['promo_count'] === $one_line['shades_count'],
            ];
        }

        return [$representatives, $meta];
    }

    /** Цена, по которой товар реально продаётся. */
    private static function effectivePrice($goods_item): float
    {
        return (float) ($goods_item->price_promo > 0 ? $goods_item->price_promo : $goods_item->price);
    }
}
