{{--
    п.3 ТЗ — «С этим товаром покупают». Макет Figma: нода 786:15289.

    Ожидает $set_goods (коллекция GoodsItemId) и $goods_item — текущий товар,
    он добавляется в комплект последней строкой с бейджем.
--}}
@php
    $set_rows = collect($set_goods)->push($goods_item);

    $set_total = 0;
    $set_total_old = 0;
    foreach ($set_rows as $one_row) {
        $row_price = getGoodsPrice($one_row);
        $has_promo = $row_price && $row_price->price_promo > 0;
        $set_total += $has_promo ? $row_price->price_promo : ($row_price->price ?? 0);
        $set_total_old += $has_promo ? $row_price->price_default : ($row_price->price ?? 0);
    }
@endphp

@if($set_rows->count() > 1)
    <div class="rec-set">
        <h2 class="rec-title">{{ trans('variables.product_bought_together') }}</h2>

        <div class="rec-set-list">
            @foreach($set_rows as $one_row)
                @php
                    $row_price = getGoodsPrice($one_row);
                    $row_has_promo = $row_price && $row_price->price_promo > 0;
                    $row_current = $row_has_promo ? $row_price->price_promo : ($row_price->price ?? 0);
                    $row_old = $row_has_promo ? $row_price->price_default : null;
                    $row_is_current = $one_row->id === $goods_item->id;
                    $row_link = route('catalog-product', ['product', $one_row->alias]);
                @endphp
                <div class="rec-set-row">
                    <div class="rec-set-photo">
                        <img src="{{ $one_row->oImage && $one_row->oImage->img && file_exists('upfiles/goods-items/m/' . showImg($one_row->oImage->img))
                                    ? asset('upfiles/goods-items/m/' . showImg($one_row->oImage->img))
                                    : asset('front-assets/img/no-image-goods-m.png') }}"
                             loading="lazy" alt="{{ $one_row->itemByLang->name ?? '' }}">
                    </div>
                    <div class="rec-set-info">
                        <div class="rec-set-head">
                            @if($row_is_current || $one_row->popular_element || $row_old)
                                <div class="rec-badges">
                                    @if($row_is_current)
                                        <span class="rec-badge rec-badge--current">{{ trans('variables.product_current_item') }}</span>
                                    @elseif($one_row->popular_element)
                                        <span class="rec-badge rec-badge--top">{{ ShowLabelById(181) }}</span>
                                    @endif
                                    @if($row_old && $row_price->promo_percent)
                                        <span class="rec-badge rec-badge--sale">-{{ $row_price->promo_percent }}%</span>
                                    @endif
                                </div>
                            @endif
                            <div class="rec-brand">{{ $one_row->getBrand->itemByLang->name ?? '' }}</div>
                            <div class="rec-name">
                                <a href="{{ $row_link }}">{{ $one_row->itemByLang->name ?? '' }}</a>
                            </div>
                        </div>
                        <div class="rec-price @if($row_old) rec-price--sale @endif">
                            <span>{{ $row_current }} {{ ShowLabelById(3) }}</span>
                            @if($row_old)
                                <span class="rec-price-old">{{ $row_old }} {{ ShowLabelById(3) }}</span>
                            @endif
                        </div>
                    </div>
                    @unless($row_is_current)
                        {{-- поштучное добавление (п.3 ТЗ); текущий товар добавляется основной кнопкой --}}
                        <a href="javascript:;" class="rec-add-one add-to-basket"
                           data-goods-item-id="{{ $one_row->id }}" data-show-notiflix="1"
                           aria-label="{{ ShowLabelById(5) }}">
                            <svg><use xlink:href="{{ asset('front-assets/svg/sprite.svg#cart') }}"></use></svg>
                        </a>
                    @endunless
                </div>
            @endforeach

            <a href="javascript:;" class="rec-set-add add-set-to-basket open-add-to-cart"
               data-goods-ids="{{ $set_rows->pluck('id')->implode(',') }}"
               data-label-added="{{ trans('variables.product_set_added') }}"
               data-label-partial="{{ trans('variables.product_set_partial') }}">
                <span class="rec-set-add-text">
                    <span class="rec-set-add-caption">{{ trans('variables.product_add_set') }}</span>
                    <span class="rec-set-add-sum">
                        <span>{{ round($set_total) }} {{ ShowLabelById(3) }}</span>
                        @if($set_total_old > $set_total)
                            <span class="rec-set-add-old">{{ round($set_total_old) }} {{ ShowLabelById(3) }}</span>
                        @endif
                    </span>
                </span>
                <span class="rec-set-add-aside">
                    <span class="rec-set-avatars">
                        @foreach($set_rows->take(3) as $one_row)
                            <img src="{{ $one_row->oImage && $one_row->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($one_row->oImage->img))
                                        ? asset('upfiles/goods-items/s/' . showImg($one_row->oImage->img))
                                        : asset('front-assets/img/no-image-goods-m.png') }}" loading="lazy" alt="">
                        @endforeach
                    </span>
                    @if($set_rows->count() > 3)
                        <span class="rec-set-more">+{{ $set_rows->count() - 3 }}</span>
                    @endif
                </span>
            </a>
        </div>
    </div>
@endif
