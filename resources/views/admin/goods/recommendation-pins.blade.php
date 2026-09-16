{{--
    Закрепление товаров в блоках рекомендаций на странице товара:
    produse_similare → «Похожие товары», produse_compatibile → «С этим товаром
    покупают». Поиск — GoodsController::searchRecommendations.

    Ожидает: $pinned_similar, $pinned_compatible — GoodsController::pinnedRecommendations();
    $exclude_id — id редактируемого товара (у нового null).
--}}
@php
    $recommendation_fields = [
        ['name' => 'produse_similare', 'label' => __('variables.products_replaced'), 'selected' => $pinned_similar],
        ['name' => 'produse_compatibile', 'label' => __('variables.products_complementary'), 'selected' => $pinned_compatible],
    ];
    $recommendations_max = \App\Services\Product\ProductRecommendations::MAX_ITEMS;
@endphp

<div class="position-relative d-flex justify-content-between mt-5">
    <div>
        <h5 class="card-title">{{ __('variables.products_replaced_and_complementary') }}</h5>
    </div>
</div>
<hr>

@foreach($recommendation_fields as $one_field)
    @php $hidden_pins = $one_field['selected']->filter(fn ($one) => $one->picker_reason); @endphp
    <div class="mb-3">
        @include('admin.templates.goods-picker', $one_field + [
            'search_url' => urlForLanguage($lang, 'searchrecommendations'),
            'exclude_id' => $exclude_id,
            'max' => $recommendations_max,
            'hint' => __('variables.recommendations_hint', ['max' => $recommendations_max]),
        ])

        {{-- закреплён раньше, а потом кончился: сохранять карточку не мешает,
             но администратор должен видеть, почему товара нет на сайте --}}
        @if($hidden_pins->isNotEmpty())
            <div class="alert alert-warning py-2 mt-2 mb-0">
                {{ __('variables.recommendations_pinned_hidden') }}
                <ul class="mb-0">
                    @foreach($hidden_pins as $one_goods)
                        <li>{{ $one_goods->itemByLang->name ?? '#' . $one_goods->id }} — {{ $one_goods->picker_reason }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endforeach
