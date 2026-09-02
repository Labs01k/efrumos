{{--
    п.6 ТЗ — палитра оттенков. Макет Figma: нода 785:11708
    (default / hover / active / active_search / active_search_no_result).

    Оттенки — отдельные товары одной линейки. Выбор ведёт на страницу оттенка:
    у каждого свой адрес, как требует ТЗ. Поиск идёт по номеру и по названию.
--}}
@if(!empty($shades) && count($shades))
    @php $active_shade = collect($shades)->firstWhere('is_current', true) ?? $shades->first(); @endphp

    <div class="pb-field pb-field--gap-8 pb-shade" data-shade-select>
        <span class="pb-field-label">{{ trans('variables.product_shade') }}</span>

        <div class="pb-shade-trigger" role="combobox" tabindex="0" aria-expanded="false">
            {{-- отдельное фото оттенка из CMS; пока его нет — кроп фото товара --}}
            @if($active_shade->shade_swatch)
                <span class="pb-swatch pb-swatch--photo" style="background-image: url('{{ $active_shade->shade_swatch }}')"></span>
            @else
                <span class="pb-swatch" style="background-image: url('{{ $active_shade->oImage && $active_shade->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($active_shade->oImage->img)) ? asset('upfiles/goods-items/s/' . showImg($active_shade->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}')"></span>
            @endif
            <span class="pb-shade-value">{{ $active_shade->shade_code }}, {{ $active_shade->shade_name }}</span>
            <input type="text" class="pb-shade-search" autocomplete="off"
                   placeholder="{{ trans('variables.product_shade_search') }}"
                   aria-label="{{ trans('variables.product_shade_search') }}">
            <svg class="pb-chevron" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M4 6l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <ul class="pb-dropdown" role="listbox">
            @foreach($shades as $one_shade)
                <li>
                    {{-- оттенка нет в наличии: приглушён, но кликабелен — линейка видна целиком --}}
                    <a class="pb-dropdown-item @if($one_shade->is_current) is-selected @endif @if(!$one_shade->in_stoc || $one_shade->products_count <= 0) is-out @endif"
                       href="{{ route('catalog-product', ['product', $one_shade->alias]) }}"
                       role="option"
                       data-code="{{ $one_shade->shade_code }}"
                       data-name="{{ $one_shade->shade_name }}">
                        @if($one_shade->shade_swatch)
                            <span class="pb-swatch pb-swatch--photo" style="background-image: url('{{ $one_shade->shade_swatch }}')"></span>
                        @else
                            <span class="pb-swatch" style="background-image: url('{{ $one_shade->oImage && $one_shade->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($one_shade->oImage->img)) ? asset('upfiles/goods-items/s/' . showImg($one_shade->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}')"></span>
                        @endif
                        <span>{{ $one_shade->shade_code }}, {{ $one_shade->shade_name }}</span>
                    </a>
                </li>
            @endforeach
            <li class="pb-dropdown-empty" hidden>{{ trans('variables.product_shade_not_found') }}</li>
        </ul>
    </div>
@endif
