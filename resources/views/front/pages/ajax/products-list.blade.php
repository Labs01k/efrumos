@if(!empty($goods_items_list) && count($goods_items_list))
    <div class="goods-item-list">
        @foreach($goods_items_list as $one_goods)
            @include('front.templates.goods-template', ['one_goods' => $one_goods])
        @endforeach
    </div>

    {{--<div class="catalog-more">
        <a href="#" class="button">încarcă mai multe</a>
    </div>--}}
    @if(!empty($new_url))
        @include('front.templates.pagination', ['paginator' => $goods_items_list, 'new_url' => $new_url])
    @else
        @include('front.templates.pagination', ['paginator' => $goods_items_list, 'new_url' => ''])
    @endif
@else
    <div class="basket-end-inner">
        <div class="basket-end-icon">
            <img src="{{ asset('front-assets/img/icons/basket-error.svg') }}" alt="Empty">
        </div>
        <div class="basket-end-title">{{ ShowLabelById(53) }}</div>
        <div class="basket-end-link">
            <a href="{{ route('/') }}">{{ ShowLabelById(54) }}</a>
        </div>
    </div>
@endif
