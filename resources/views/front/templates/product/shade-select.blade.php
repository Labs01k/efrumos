{{--
    п.6 ТЗ — палитра оттенков. Макет Figma: нода 785:11708
    (default / hover / active / active_search / active_search_no_result).

    Оттенки — отдельные товары одной линейки. Выбор ведёт на страницу оттенка:
    у каждого свой адрес, как требует ТЗ. Поиск идёт по номеру и по названию.
--}}
@if(!empty($shades) && count($shades))
    @php $active_shade = collect($shades)->firstWhere('is_current', true) ?? $shades->first(); @endphp

    <div class="pb-field pb-field--gap-8 pb-shade" data-shade-select data-image-base="{{ asset('upfiles') }}/">
        <span class="pb-field-label">{{ trans('variables.product_shade') }}</span>

        <div class="pb-shade-trigger" role="combobox" tabindex="0" aria-expanded="false">
            {{-- отдельное фото оттенка из CMS; пока его нет — кроп фото товара --}}
            <span class="pb-swatch{{ $active_shade->shade_swatch ? ' pb-swatch--photo' : '' }}" style="background-image: url('{{ \App\Services\Product\ShadePalette::cardImageUrl($active_shade, 's', 'no-image-xs.png') }}')"></span>
            <span class="pb-shade-value">{{ $active_shade->shade_label }}</span>
            <input type="text" class="pb-shade-search" autocomplete="off"
                   placeholder="{{ trans('variables.product_shade_search') }}"
                   aria-label="{{ trans('variables.product_shade_search') }}">
            <svg class="pb-chevron" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M4 6l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        {{--
            Список печатается без отступов и переносов: в линейке бывает 125+
            оттенков, и каждый лишний пробел в строке — это килобайты на
            странице. Ссылки оставляем в разметке: по ним ходят и покупатель
            без JavaScript, и поисковик (перелинковка внутри линейки).
            Свотчи скрытого списка браузер не загружает, пока его не откроют.

            data-title и data-images нужны для мгновенной смены оттенка: по ним
            JS сразу меняет заголовок и галерею (фото предзагружены при открытии
            списка и наведении), а остальное подтягивает страница оттенка.
            В data-images — пути от upfiles/ (фото оттенка из CMS первым, как в
            галерее — ShadePalette::galleryImages), база одна на весь список.
        --}}
        <ul class="pb-dropdown" role="listbox">
            @foreach($shades as $one_shade)
                @php
                    $shade_classes = ($one_shade->is_current ? ' is-selected' : '')
                        . (!$one_shade->in_stoc || $one_shade->products_count <= 0 ? ' is-out' : '');
                    $shade_title = $one_shade->shade_label;
                    $shade_photos = \App\Services\Product\ShadePalette::galleryImages($one_shade)->pluck('path')->implode(',');
                    $shade_image = \App\Services\Product\ShadePalette::cardImageUrl($one_shade, 's', 'no-image-xs.png');
                @endphp
                <li><a class="pb-dropdown-item{{ $shade_classes }}" href="{{ route('catalog-product', ['product', $one_shade->alias]) }}" role="option" data-code="{{ $one_shade->shade_code }}" data-name="{{ $one_shade->shade_name }}" data-title="{{ $one_shade->itemByLang->name ?? '' }}" data-images="{{ $shade_photos }}"><span class="pb-swatch{{ $one_shade->shade_swatch ? ' pb-swatch--photo' : '' }}" style="background-image:url({{ $shade_image }})"></span><span>{{ $shade_title }}</span></a></li>
            @endforeach
            <li class="pb-dropdown-empty" hidden>{{ trans('variables.product_shade_not_found') }}</li>
        </ul>
    </div>
@endif
