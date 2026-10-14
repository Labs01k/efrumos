{{--
    п.5 ТЗ — наличие товара по магазинам. Макет Figma: ноды 785:11439 (выбор города),
    785:11323 (список магазинов).

    Ожидает $shops_stock — коллекцию строк вида
    ['name', 'city', 'address', 'qty', 'in_stock', 'lat', 'lng']; с правки от
    14.10.2026 в ней уже только магазины с реальным остатком (byShops() сам
    их фильтрует) — бейджа «нет в наличии» и кнопки «показать ещё» больше нет,
    по прямому запросу клиента не вводить в заблуждение количеством магазинов,
    которые нечем закрыть.

    $online_available (bool) — остаток главного склада комплектации
    (ProductStock::onlineAvailable()), показывается отдельной строкой
    «В наличии для онлайн-заказа», не как магазин в списке городов.
--}}
@if((!empty($shops_stock) && count($shops_stock)) || $online_available)
    @if($online_available)
        <div class="pb-field pb-field--gap-8 pb-online-stock">
            <span class="pb-shop-status pb-shop-status--online">{{ trans('variables.product_online_stock') }}</span>
        </div>
    @endif

    @if(!empty($shops_stock) && count($shops_stock))
    @php
        // Кишинёв первым (как в макете), остальные города по алфавиту
        $stock_cities = collect($shops_stock)->pluck('city')->filter()->unique()
            ->sort(function ($a, $b) {
                $a_main = mb_stripos($a, 'Кишин') !== false || mb_stripos($a, 'Chi') === 0;
                $b_main = mb_stripos($b, 'Кишин') !== false || mb_stripos($b, 'Chi') === 0;

                return $a_main !== $b_main ? ($a_main ? -1 : 1) : mb_strtolower($a) <=> mb_strtolower($b);
            })
            ->values();
        $stock_active_city = $stock_cities->first();
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
    <div class="pb-shops" data-shops data-goods-item-id="{{ $goods_item->id }}">
        <div class="pb-shops-list">
            @foreach($shops_stock as $one_shop)
                <div class="pb-shop-item"
                     data-shop-id="{{ $one_shop['shop']->id ?? '' }}"
                     data-city="{{ $one_shop['city'] }}"
                     data-lat="{{ $one_shop['lat'] ?? '' }}"
                     data-lng="{{ $one_shop['lng'] ?? '' }}"
                     @if($one_shop['city'] !== $stock_active_city) hidden @endif>
                    <div class="pb-shop-title">
                        <span>{{ $one_shop['name'] }}</span>
                        <span>–</span>
                        <span class="pb-shop-status">{{ trans('variables.product_in_stock') }}</span>
                    </div>
                    <div class="pb-shop-address">{{ $one_shop['address'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
@endif
