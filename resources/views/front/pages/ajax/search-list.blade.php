@if(!empty($keywords_search) && count($keywords_search))
    <div class="search-results-links">
        <ul>
            @foreach($keywords_search as $one_keyword)
                <li>
                    <a href="{{ $one_keyword->itemByLang->link ?? '' }}">{!! str_replace(mb_strtolower($search_value), '<strong>'.mb_strtolower($search_value).'</strong>', mb_strtolower($one_keyword->itemByLang->name)) !!}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if(!empty($search_goods_subject) && count($search_goods_subject))
    <div class="search-results-title">{{ ShowLabelById(232) }}</div>
    <div class="search-results-links">
        <ul>
            @foreach($search_goods_subject as $one_goods_subject)
                <li>
                    <a href="{{ route('category', $one_goods_subject->alias) }}">{!! str_replace(mb_strtolower($search_value), '<strong>'.mb_strtolower($search_value).'</strong>', mb_strtolower($one_goods_subject->itemByLang->name)) !!}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if(!empty($search_goods_items) && count($search_goods_items))
    <div class="search-results-title">{{ ShowLabelById(233) }}</div>
    <div class="search-results-list">
        @foreach($search_goods_items as $one_goods)
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
                    <div
                        class="search-results-price">{{ $goods_price_collect->price ?? '' }} {{ ShowLabelById(3) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="search-results-footer">
        <a href="{{ route('catalog-product') }}?s={{ $search_value ?? '' }}" class="button button--black">{{ ShowLabelById(234) }}</a>
    </div>
@endif

@if($search_goods_items->isEmpty() && $search_goods_subject->isEmpty())
    <div class="search-results-footer">
        <p>{{ ShowLabelById(235) }} <strong>{{ $search_value ?? '' }}</strong>:(</p>
    </div>
@endif





