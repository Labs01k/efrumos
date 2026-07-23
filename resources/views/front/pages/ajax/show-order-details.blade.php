<div class="order-details-inner">
    <div class="order-details-head">{{ ShowLabelById(84) }} #{{ $order->id ?? '' }}</div>
    <div class="order-details-info">
        <div class="order-details-col">
            <p><b>{{ ShowLabelById(84) }}</b></p>
            <p>{{ ShowLabelById(85) }}: {{ getDefaultDateFormat($order->created_at) }}</p>
            <p>{{ ShowLabelById(74) }}: achitat</p>
            <p>{{ ShowLabelById(86) }}: tracking</p>
        </div>
        <div class="order-details-col">
            <p><b>{{ ShowLabelById(87) }}</b></p>
            <p>{{ $user->email ?? '' }}</p>
            <p>{{ $user->last_name ?? '' }} {{ $user->name ?? '' }}</p>
            <p>{{ $user->phone ?? '' }}</p>
            <p>{{ $user_district->name ?? '' }}, {{ $user->city ?? '' }} </p>
            <p>{{ $user->address ?? '' }}</p>
        </div>
        <div class="order-details-col">
            <p><b>{{ ShowLabelById(207) }}</b></p>
            <p>{{ getEnumValueName($order->delivery_method) }} - {{ $order->ordersData->delivery_cost ?? '' }} {{ ShowLabelById(3) }}</p>
        </div>
    </div>
    @if($order && $order->basket->isNotEmpty())
        <div class="order-details-title">{{ ShowLabelById(77) }}</div>
        <div class="order-details-caption">
            <div class="order-details-img">{{ ShowLabelById(78) }}: {{ $order->basket->count() }}</div>
            <div class="order-details-text">{{ ShowLabelById(79) }}</div>
            <div class="order-details-quantity">{{ ShowLabelById(80) }}</div>
            <div class="order-details-price">{{ ShowLabelById(81) }}</div>
        </div>
        <div class="order-details-list">
            @foreach($order->basket as $one_basket_item)
                <div class="order-details-item">
                    <div class="order-details-img">
                        <a href="{{ route('catalog-product', ['product', $one_basket_item->goodsItemId->alias]) }}">
                            <img
                                src="{{ $one_basket_item->goodsItemId->oImage && $one_basket_item->goodsItemId->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($one_basket_item->goodsItemId->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($one_basket_item->goodsItemId->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}"
                                alt="{{ $one_basket_item->goodsItemId->itemByLang->name ?? '' }}">
                        </a>
                    </div>
                    <div class="order-details-text">
                        <div class="order-details-name">{{ $one_basket_item->goodsItemId->itemByLang->name ?? '' }}
                        </div>
                        @if($one_basket_item->goodsItemId->articol)
                            <div class="order-details-desc">
                                {{ ShowLabelById(9) }}: {{ $one_basket_item->goodsItemId->articol ?? '' }}</div>
                        @endif
                    </div>
                    <div class="order-details-quantity">{{ $one_basket_item->items_count ?? '' }} x {{ getDefaultPriceFormat($one_basket_item->goods_price) }} {{ ShowLabelById(3) }}</div>
                    <div class="order-details-price">{{ getDefaultPriceFormat($one_basket_item->items_count * $one_basket_item->goods_price) }} {{ ShowLabelById(3) }}</div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="order-details-total">
        <p>{{ ShowLabelById(82) }}: {{ $order->ordersData->delivery_cost ?? '' }} {{ ShowLabelById(3) }}</p>
        <p>{{ ShowLabelById(67) }}: {{ getDefaultPriceFormat($order->ordersData->total_price + $order->ordersData->delivery_cost) }} {{ ShowLabelById(3) }}</p>
    </div>
    <div class="order-details-footer">
        <a href="javascript:;" class="button button--black repeat-order" data-order-id="{{ $order->id ?? '' }}">{{ ShowLabelById(83) }}</a>
    </div>
</div>
