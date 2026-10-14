{{--
    п.3 тикета 14.10.2026 — подбор окислителя/активатора для краски.
    Ожидает $oxidizers — коллекцию товаров-окислителей (GoodsItemId,
    с посчитанным oxidizer_percent), и $goods_item — текущую краску.
    Пусто = блок скрыт (OxidizerMatcher::compatibleProducts() пустая
    коллекция, если краска не требует окислителя или он не подобрался).

    Процент не выбирается автоматически (явное требование клиента) —
    первый пункт списка открыт только потому, что <select> должен на
    чём-то стоять изначально, назначение остаётся на покупателе.
--}}
@if($oxidizers->isNotEmpty())
    <div class="section pt-0">
        <div class="container">
            <div class="pb-field pb-field--gap-8 pb-oxidizer" data-oxidizer-picker>
                <span class="pb-field-label">{{ trans('variables.product_oxidizer_label') }}</span>

                <select class="form-select" data-oxidizer-select>
                    @foreach($oxidizers as $one_oxidizer)
                        <option value="{{ $one_oxidizer->id }}">
                            {{ $one_oxidizer->oxidizer_percent !== null ? $one_oxidizer->oxidizer_percent.'%' : '' }}
                            @if($one_oxidizer->gramaj) · {{ $one_oxidizer->gramaj }} {{ trans('variables.ml_short') }} @endif
                        </option>
                    @endforeach
                </select>

                <a href="javascript:;" class="rec-set-add add-set-to-basket"
                   data-goods-ids="{{ $goods_item->id }},{{ $oxidizers->first()->id }}"
                   data-oxidizer-dye-id="{{ $goods_item->id }}"
                   data-show-notiflix="1">
                    {{ trans('variables.product_oxidizer_add') }}
                </a>
            </div>
        </div>
    </div>
@endif
