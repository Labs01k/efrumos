{{--
    п.4 ТЗ — «Похожие товары». Макет Figma: нода 786:15677.
    Ожидает $similar_goods — коллекцию GoodsItemId.
--}}
@if(!empty($similar_goods) && count($similar_goods))
    <div class="rec-similar" data-rec-slider>
        <div class="rec-similar-head">
            <h2 class="rec-title">{{ trans('variables.product_similar') }}</h2>
            <button type="button" class="rec-similar-nav" data-rec-prev aria-label="{{ trans('variables.product_slider_prev') }}">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M13 8H3M6.5 3.5 3 8l3.5 4.5"/></svg>
            </button>
            <button type="button" class="rec-similar-nav" data-rec-next aria-label="{{ trans('variables.product_slider_next') }}">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M9.5 3.5 13 8l-3.5 4.5"/></svg>
            </button>
        </div>

        <div class="rec-similar-track">
            @foreach($similar_goods as $one_goods)
                @include('front.templates.product.product-card', ['one_goods' => $one_goods])
            @endforeach
        </div>
    </div>
@endif
