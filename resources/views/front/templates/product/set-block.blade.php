{{--
    п.3 ТЗ — «С этим товаром покупают». Макет Figma: нода 786:15289.

    Ожидает $set_goods (коллекция GoodsItemId) и $goods_item — текущий товар,
    он добавляется в комплект последней строкой с бейджем. $oxidizers —
    опционально, полный список окислителей линейки (для строки с инлайн-
    выбором процента, см. ниже) — $set_goods уже содержит ОДИН окислитель-
    представитель (ProductRecommendations::boughtTogether()), этот список
    только для select'а вариантов в его строке.

    Правка от 16.10.2026 (Site_3.docx, картинка 2): окислитель — обычная
    строка в этом же списке (фото/название/цена, как у всех), не отдельный
    блок поверх него — просто у его строки есть свой select процента,
    который на лету меняет, какой именно товар добавляется в корзину.
--}}
@php
    $set_rows = collect($set_goods)->push($goods_item);
    $oxidizer_ids = collect($oxidizers ?? [])->pluck('id');
    // по товару внутри процента отдаём первый — коллекция уже отсортирована
    // (процент, затем объём) в OxidizerMatcher::compatibleProducts()
    $oxidizer_percent_groups = collect($oxidizers ?? [])->groupBy(fn ($one) => $one->oxidizer_percent ?? 'other');

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
                    $row_is_oxidizer = $oxidizer_ids->contains($one_row->id);
                @endphp
                <div class="rec-set-row"@if($row_is_oxidizer) data-oxidizer-row data-oxidizer-dye-id="{{ $goods_item->id }}"@endif>
                    <div class="rec-set-photo" data-oxidizer-row-photo>
                        <img src="{{ \App\Services\Product\ShadePalette::cardImageUrl($one_row) }}"
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
                            <div class="rec-name" data-oxidizer-row-name>
                                <a href="{{ $row_link }}">{{ $one_row->itemByLang->name ?? '' }}</a>
                            </div>
                        </div>
                        @if($row_is_oxidizer && $oxidizer_percent_groups->count() > 1)
                            {{-- Site_3.docx, картинка 2 — select процента прямо в строке
                                 набора; меняет, какой именно товар строка представляет
                                 (их фото/название/цена ниже обновляет initOxidizerSetRow()) --}}
                            <label class="rec-set-oxidizer-label">
                                {{ trans('variables.product_oxidizer_percent_label') }}
                                <select class="form-select rec-set-oxidizer-select" data-oxidizer-row-select
                                        data-total-base="{{ round($set_total - $row_current) }}"
                                        data-currency="{{ ShowLabelById(3) }}">
                                    @foreach($oxidizer_percent_groups as $percent => $group)
                                        @php $one_option = $group->first(); @endphp
                                        <option value="{{ $one_option->id }}"
                                                data-image="{{ \App\Services\Product\ShadePalette::cardImageUrl($one_option) }}"
                                                data-name="{{ $one_option->itemByLang->name ?? '' }}"
                                                data-link="{{ route('catalog-product', ['product', $one_option->alias]) }}"
                                                data-price="{{ round(getGoodsPrice($one_option)->price ?? 0) }}"
                                                {{ $one_option->id === $one_row->id ? 'selected' : '' }}>
                                            {{ $percent }}%
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                        <div class="rec-price @if($row_old) rec-price--sale @endif" data-oxidizer-row-price>
                            <span>{{ $row_current }} {{ ShowLabelById(3) }}</span>
                            @if($row_old)
                                <span class="rec-price-old">{{ $row_old }} {{ ShowLabelById(3) }}</span>
                            @endif
                        </div>
                    </div>
                    @unless($row_is_current)
                        {{-- поштучное добавление (п.3 ТЗ); текущий товар добавляется основной кнопкой --}}
                        <a href="javascript:;" class="rec-add-one add-to-basket" data-oxidizer-row-add
                           data-goods-item-id="{{ $one_row->id }}" data-show-notiflix="1"
                           aria-label="{{ ShowLabelById(5) }}">
                            <svg><use xlink:href="{{ asset('front-assets/svg/sprite.svg#cart') }}"></use></svg>
                        </a>
                    @endunless
                </div>
            @endforeach

            {{-- без open-add-to-cart: та модалка про один товар и открывалась
                 пустой на всё время запроса; по ответу открывается корзина сбоку --}}
            <a href="javascript:;" class="rec-set-add add-set-to-basket"
               data-goods-ids="{{ $set_rows->pluck('id')->implode(',') }}"
               data-label-partial="{{ trans('variables.product_set_partial') }}"
               data-label-error="{{ trans('variables.product_set_error') }}"
               @if($oxidizer_ids->isNotEmpty()) data-oxidizer-row-set @endif>
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
                            <img src="{{ \App\Services\Product\ShadePalette::cardImageUrl($one_row, 's') }}" loading="lazy" alt="">
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
