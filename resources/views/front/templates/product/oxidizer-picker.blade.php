{{--
    п.3 тикета 14.10.2026 + правка 16.10.2026 — клиент назвал исходный
    пикер «не читабельным» и попросил сделать как переключатель оттенков
    (с картинкой) и дать выбрать процент и объём отдельно.

    Ожидает $oxidizers — коллекцию товаров-окислителей (GoodsItemId, с
    oxidizer_percent/oxidizer_volume_label из OxidizerMatcher::compatibleProducts()),
    и $goods_item — текущую краску. Пусто = блок скрыт.

    Два отдельных контрола: процент — свотч-дропдаун с фото, визуально и
    по интерактиву как pb-shade у переключателя оттенков (только без
    перехода на другую страницу — тут меняется не товар, а data-goods-ids
    у кнопки "добавить вместе"); объём — обычный select, список сам
    сужается до вариантов выбранного процента. Если у линейки всего один
    процент — сам свотч-пикер не нужен, остаётся только объём.

    Процент не выбирается автоматически (явное требование клиента) —
    первая позиция открыта только потому, что селектору нужно на чём-то
    стоять изначально, назначение остаётся на покупателе.
--}}
@if($oxidizers->isNotEmpty())
    @php
        $percent_groups = $oxidizers->groupBy(fn ($one) => $one->oxidizer_percent ?? 'other');
        $default_percent = $percent_groups->keys()->first();
        $default_product = $percent_groups->first()->first();
    @endphp
    <div class="section pt-0">
        <div class="container">
            <div class="pb-field pb-field--gap-8 pb-oxidizer" data-oxidizer-picker data-oxidizer-dye-id="{{ $goods_item->id }}">
                <span class="pb-field-label">{{ trans('variables.product_oxidizer_label') }}</span>

                <div class="pb-oxidizer-row">
                    @if($percent_groups->count() > 1)
                        <div class="pb-shade pb-oxidizer-percent" data-oxidizer-percent>
                            <div class="pb-shade-trigger" role="combobox" tabindex="0" aria-expanded="false">
                                <span class="pb-swatch pb-swatch--photo" style="background-image:url('{{ \App\Services\Product\ShadePalette::cardImageUrl($default_product, 's', 'no-image-xs.png') }}')"></span>
                                <span class="pb-shade-value">{{ $default_percent }}%</span>
                                <svg class="pb-chevron" viewBox="0 0 16 16" aria-hidden="true">
                                    <path d="M4 6l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <ul class="pb-dropdown" role="listbox">
                                @foreach($percent_groups as $percent => $group)
                                    @php $sample = $group->first(); @endphp
                                    <li>
                                        <a href="javascript:;" class="pb-dropdown-item{{ (string) $percent === (string) $default_percent ? ' is-selected' : '' }}" role="option"
                                           data-percent="{{ $percent }}"
                                           data-image="{{ \App\Services\Product\ShadePalette::cardImageUrl($sample, 's', 'no-image-xs.png') }}">
                                            <span class="pb-swatch pb-swatch--photo" style="background-image:url('{{ \App\Services\Product\ShadePalette::cardImageUrl($sample, 's', 'no-image-xs.png') }}')"></span>
                                            <span>{{ $percent }}%</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <select class="form-select pb-oxidizer-volume" data-oxidizer-volume-select>
                        @foreach($oxidizers as $one_oxidizer)
                            <option value="{{ $one_oxidizer->id }}"
                                    data-percent="{{ $one_oxidizer->oxidizer_percent }}"
                                    {{ $one_oxidizer->id === $default_product->id ? 'selected' : '' }}
                                    {{ (string) $one_oxidizer->oxidizer_percent !== (string) $default_percent ? 'hidden' : '' }}>
                                {{ $one_oxidizer->oxidizer_volume_label ?? $one_oxidizer->itemByLang->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <a href="javascript:;" class="rec-set-add add-set-to-basket"
                   data-goods-ids="{{ $goods_item->id }},{{ $default_product->id }}"
                   data-oxidizer-dye-id="{{ $goods_item->id }}"
                   data-show-notiflix="1">
                    {{ trans('variables.product_oxidizer_add') }}
                </a>
            </div>
        </div>
    </div>
@endif
