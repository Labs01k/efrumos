<?php

namespace App\Services\Admin;

use App\Models\GoodsItem;
use App\Models\GoodsItemId;
use Illuminate\Support\Collection;

/**
 * Выбор товаров в формах CMS (рекомендации в карточке товара, товары
 * инфоленты): поиск для select2 ajax и уже выбранные товары для разметки.
 * Весь каталог — ~4,9 тыс. товаров — в форму больше не грузится: раньше
 * каждый такой селект рендерил их все, и форма тормозила.
 */
class GoodsPicker
{
    public const PER_PAGE = 20;

    /**
     * Страница результатов поиска. Поиск по названию на любом языке, 1С-коду,
     * артикулу и ID; точное совпадение ID, кода или артикула — первым.
     *
     * @param callable|null $order_query дополнительная сортировка до сортировки по id
     * @return array{0: Collection, 1: bool} товары страницы и есть ли следующая
     */
    public static function search(string $q, int $page, ?int $exclude_id = null, ?callable $order_query = null): array
    {
        $q = trim($q);
        $page = max(1, $page);

        $found = GoodsItemId::where('deleted', 0)
            ->where('active', 1)
            ->when($exclude_id, fn ($query) => $query->where('id', '!=', $exclude_id))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    // название на любом языке: ищут так, как помнят товар
                    $sub->whereIn('id', GoodsItem::select('goods_item_id')->where('name', 'like', '%' . $q . '%'))
                        ->orWhere('one_c_code', 'like', $q . '%')
                        ->orWhere('articol', 'like', '%' . $q . '%');

                    if (ctype_digit($q)) {
                        $sub->orWhere('id', (int) $q);
                    }
                });
            })
            ->with('itemByLang')
            // «612» ищут конкретный товар, а не всё, где эти цифры встречаются
            ->when($q !== '', fn ($query) => $query->orderByRaw('(id = ? OR one_c_code = ? OR articol = ?) DESC', [ctype_digit($q) ? (int) $q : 0, $q, $q]))
            ->when($order_query, fn ($query) => $order_query($query))
            ->orderBy('id', 'desc')
            ->skip(($page - 1) * self::PER_PAGE)
            ->take(self::PER_PAGE + 1)
            ->get();

        return [$found->take(self::PER_PAGE)->values(), $found->count() > self::PER_PAGE];
    }

    /** Выбранные товары из поля «1,2,3» в сохранённом порядке. */
    public static function byIds(?string $ids): Collection
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $ids)))));

        if (!$ids) {
            return collect();
        }

        return GoodsItemId::whereIn('id', $ids)
            ->with('itemByLang')
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
            ->get();
    }

    /** «Название | 1С: код | арт.: артикул» — как товар ищут в CMS. */
    public static function label(GoodsItemId $goods_item): string
    {
        return collect([
            $goods_item->itemByLang->name ?? ('#' . $goods_item->id),
            $goods_item->one_c_code ? '1С: ' . $goods_item->one_c_code : null,
            $goods_item->articol ? __('variables.recommendations_articol') . ': ' . $goods_item->articol : null,
        ])->filter()->implode(' | ');
    }

    /** Строки select2 для поиска, у рекомендаций — с причиной, почему товар выбрать нельзя. */
    public static function results(Collection $goods, ?callable $reason = null): Collection
    {
        return $goods->map(function ($one_goods) use ($reason) {
            $why_not = $reason ? $reason($one_goods) : null;

            return [
                'id' => $one_goods->id,
                'text' => self::label($one_goods),
                'disabled' => $why_not !== null,
                'reason' => $why_not,
            ];
        })->values();
    }
}
