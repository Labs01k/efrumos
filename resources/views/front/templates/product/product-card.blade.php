{{--
    Карточка товара в блоках рекомендаций. Макет Figma: нода 786:15718.
    Ожидает $one_goods — модель GoodsItemId с itemByLang, oImage, getBrand.
--}}
@php
    $card_price = getGoodsPrice($one_goods);
    $card_has_promo = $card_price && $card_price->price_promo > 0;
    $card_current = $card_has_promo ? $card_price->price_promo : ($card_price->price ?? 0);
    $card_old = $card_has_promo ? $card_price->price_default : null;
    $card_link = route('catalog-product', ['product', $one_goods->alias]);
@endphp

<div class="rec-card">
    <div class="rec-card-photo">
        @if($one_goods->popular_element || $card_has_promo)
            <div class="rec-badges">
                @if($one_goods->popular_element)
                    <span class="rec-badge rec-badge--top">{{ ShowLabelById(181) }}</span>
                @endif
                @if($card_has_promo && $card_price->promo_percent)
                    <span class="rec-badge rec-badge--sale">-{{ $card_price->promo_percent }}%</span>
                @endif
            </div>
        @endif
        <a href="{{ $card_link }}">
            <img src="{{ $one_goods->oImage && $one_goods->oImage->img && file_exists('upfiles/goods-items/m/' . showImg($one_goods->oImage->img))
                        ? asset('upfiles/goods-items/m/' . showImg($one_goods->oImage->img))
                        : asset('front-assets/img/no-image-goods-m.png') }}"
                 loading="lazy" alt="{{ $one_goods->itemByLang->name ?? '' }}">
        </a>
    </div>

    <div class="rec-card-info">
        <div class="rec-brand">{{ $one_goods->getBrand->itemByLang->name ?? '' }}</div>
        <div class="rec-name">
            <a href="{{ $card_link }}">{{ $one_goods->itemByLang->name ?? '' }}</a>
        </div>
    </div>

    <div class="rec-price @if($card_old) rec-price--sale @endif">
        <span>{{ $card_current }} {{ ShowLabelById(3) }}</span>
        @if($card_old)
            <span class="rec-price-old">{{ $card_old }} {{ ShowLabelById(3) }}</span>
        @endif
    </div>
</div>
