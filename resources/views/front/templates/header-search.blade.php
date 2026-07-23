<div class="search-results">
    @if($popular_search && $popular_search->children->isNotEmpty())
        <div class="search-results-links default-search-popular d-none">
            <ul>
                @foreach($popular_search->children as $one_popular_search_item)
                    <li>
                        <a href="{{ $one_popular_search_item->itemByLang->link ?? '' }}">{{ $one_popular_search_item->itemByLang->name ?? '' }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(!empty($show_in_search_goods) && count($show_in_search_goods))
        <div class="default-search-products d-none">
            <div class="search-results-title">{{ ShowLabelById(233) }}</div>
            <div class="search-results-list">
                @foreach($show_in_search_goods as $one_goods)
                    @php
                        $goods_price_collect = getGoodsPrice($one_goods);
                    @endphp
                    <div class="search-results-item">
                        <div class="search-results-img">
                            <a href="{{ route('catalog-product', ['product', $one_goods->alias]) }}">
                                <img
                                    src="{{ $one_goods->oImage && $one_goods->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($one_goods->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($one_goods->oImage->img)) : asset('front-assets/img/no-image-goods-m.png') }}"
                                    loading="lazy"
                                    alt="{{ $one_goods->itemByLang->name ?? '' }}">
                            </a>
                        </div>
                        <div class="search-results-content">
                            <div class="search-results-name">
                                <a href="{{ route('catalog-product', ['product', $one_goods->alias]) }}">{{ $one_goods->itemByLang->name ?? '' }}</a>
                            </div>
                            <div class="search-results-price">{{ $goods_price_collect->price ?? '' }} {{ ShowLabelById(3) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="search-results-footer"></div>
        </div>
    @endif

    <div class="render-all-search"></div>
</div>
