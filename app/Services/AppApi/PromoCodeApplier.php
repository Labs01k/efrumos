<?php

namespace App\Services\AppApi;

use App\Models\GoodsPromo;
use App\Models\GoodsPromoItems;

/**
 * EFA-82 / E5-BES-02 — промокоды для приложения: «Состояния: применён,
 * неверный, истёк, не действует на товары; снятие промокода» (текст
 * задачи буквально). Приложение стейтлес, как и весь OrderPricing —
 * «снятие» промокода — это просто следующий quote() без promo_code,
 * сервер между запросами ничего не хранит, отдельный endpoint на снятие
 * не нужен.
 *
 * Логика повторяет Front\CartController::ajaxCheckPromoCode (сайт), но
 * против переданных $lines (OrderPricing::quote()), а не cookie-корзины —
 * сайт не трогаем, код продублирован сознательно (тот же принцип, что в
 * самом OrderPricing).
 *
 * НЕ реализовано: промо-тип «подарок» (goods_promo.cant_cadou) — это
 * добавление товара в заказ, а не скидка, другой механизм показа в
 * приложении (нет в тексте задачи). Такой код считается не применимым к
 * товарам, а не «применён без эффекта».
 *
 * Скидка на совпавшую строку: discount_procent — процент от цены строки;
 * discount_summa — сумма, вычитаемая из цены ЕДИНИЦЫ товара, умноженная
 * на количество (как в Front\CartController::ajaxGetBasketGoods, строка
 * с `$item_discount_price = price - discount_summa`, не как в более
 * старом/расходящемся коде OrderController — он так же, если смотреть
 * внимательно, просто другим путём к тому же числу).
 */
final class PromoCodeApplier
{
    /**
     * @param QuoteLine[] $lines
     */
    public static function apply(string $code, array $lines): PromoCodeResult
    {
        $promo = GoodsPromo::where('promocod', $code)->where('tip_price', '>=', 'b2c')->first();

        if (!$promo) {
            return new PromoCodeResult(PromoCodeResult::INVALID, $code);
        }

        $now = time();
        $start = strtotime((string) $promo->data_start);
        $end = strtotime((string) $promo->data_end);

        if ($start > $now || $end < $now) {
            return new PromoCodeResult(PromoCodeResult::EXPIRED, $code);
        }

        if ((float) $promo->cant_cadou > 0) {
            return new PromoCodeResult(PromoCodeResult::NOT_APPLICABLE_TO_ITEMS, $code);
        }

        $promoOneCIds = GoodsPromoItems::where('goods_promo_id', $promo->id)->pluck('one_c_id')->all();
        $minQty = (int) $promo->cant_pentru_disc;

        $discount = 0;
        $matchedProductIds = [];

        foreach ($lines as $line) {
            $goods = $line->goods;

            $eligible = (float) ($goods->price_promo ?? 0) == 0.0
                && in_array($goods->one_c_code, $promoOneCIds, true)
                && (!$minQty || $line->qty >= $minQty);

            if (!$eligible) {
                continue;
            }

            $matchedProductIds[] = $goods->id;

            if ($promo->discount_procent > 0) {
                $discount += (int) round($line->total() * $promo->discount_procent / 100);
            } elseif ($promo->discount_summa > 0) {
                $discount += (int) round(min($line->unitPrice, $promo->discount_summa) * $line->qty);
            }
        }

        if ($matchedProductIds === []) {
            return new PromoCodeResult(PromoCodeResult::NOT_APPLICABLE_TO_ITEMS, $code);
        }

        return new PromoCodeResult(PromoCodeResult::APPLIED, $code, $discount, $matchedProductIds, (int) $promo->one_c_id);
    }
}
