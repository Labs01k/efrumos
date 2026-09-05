{{--
    п.5 ТЗ — наличие товара по магазинам. Макет Figma: ноды 785:11439 (выбор города),
    785:11323 (список магазинов), 785:11337 (кнопка раскрытия).

    Ожидает $shops_stock — коллекцию строк вида
    ['name', 'city', 'address', 'qty', 'in_stock', 'lat', 'lng'].
    Пока 1С не отдаёт остатки в разрезе складов, коллекция пустая и блок не выводится.
--}}
@if(!empty($shops_stock) && count($shops_stock))
    @php
        $stock_cities = collect($shops_stock)->pluck('city')->filter()->unique()->sort()->values();
        $stock_active_city = $stock_cities->first();
        $stock_has_empty = collect($shops_stock)->where('in_stock', false)->count() > 0;
        // товара нет ни в одном магазине: показываем весь список сразу, без сворачивания —
        // иначе свёрнутый вид (только магазины с наличием) был бы пустым
        $stock_all_out = collect($shops_stock)->where('in_stock', true)->isEmpty();
    @endphp

    <div class="pb-field pb-field--gap-8 pb-city" data-city-select>
        <span class="pb-field-label">{{ trans('variables.product_stock_in_cities') }}</span>

        <div class="pb-city-trigger" role="combobox" tabindex="0" aria-expanded="false">
            <span class="pb-city-value">{{ $stock_active_city }}</span>
            <svg class="pb-chevron" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M4 6l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <ul class="pb-dropdown" role="listbox">
            <li>
                <a class="pb-dropdown-item" href="javascript:;"
                   data-city="{{ trans('variables.product_all_shops') }}" role="option">
                    {{ trans('variables.product_all_shops') }}
                </a>
            </li>
            @foreach($stock_cities as $one_city)
                <li>
                    <a class="pb-dropdown-item @if($one_city === $stock_active_city) is-selected @endif"
                       href="javascript:;" data-city="{{ $one_city }}" role="option">{{ $one_city }}</a>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- data-goods-item-id — для запроса ближайшего магазина с наличием
         (GET ajaxNearestShopWithStock, см. frontend-spec.md, Epic 5) --}}
    <div class="pb-shops @if($stock_all_out) pb-shops--all-out @endif"
         data-shops data-goods-item-id="{{ $goods_item->id }}">
        <div class="pb-shops-list">
            @foreach($shops_stock as $one_shop)
                <div class="pb-shop-item @if(!$one_shop['in_stock']) is-out @endif"
                     data-shop-id="{{ $one_shop['shop']->id ?? '' }}"
                     data-city="{{ $one_shop['city'] }}"
                     data-lat="{{ $one_shop['lat'] ?? '' }}"
                     data-lng="{{ $one_shop['lng'] ?? '' }}"
                     @if($one_shop['city'] !== $stock_active_city) hidden @endif>
                    <div class="pb-shop-title">
                        <span>{{ $one_shop['name'] }}</span>
                        <span>–</span>
                        <span class="pb-shop-status">
                            {{ $one_shop['in_stock'] ? trans('variables.product_in_stock') : trans('variables.product_out_of_stock') }}
                        </span>
                    </div>
                    <div class="pb-shop-address">{{ $one_shop['address'] }}</div>
                </div>
            @endforeach
        </div>

        @if($stock_has_empty && !$stock_all_out)
            <button type="button" class="pb-shops-toggle">
                <span class="pb-shops-toggle-more">{{ trans('variables.product_shops_expand') }}</span>
                <span class="pb-shops-toggle-less">{{ trans('variables.product_shops_collapse') }}</span>
                <svg class="pb-chevron-sm" viewBox="0 0 12 12" aria-hidden="true">
                    <path d="M3 7.5l3-3 3 3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        @endif
    </div>
@endif
