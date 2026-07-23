<div class="catalog-page-result">
    <p>{{ ShowLabelById(244) }}:
        <span class="goods-items-count">{{ $goods_items_list && $goods_items_list->total() ? $goods_items_list->total() : 0 }} {{ $goods_items_list ? strtolower(trans_choice('variables.goods', $goods_items_list->total())) : '' }}</span>
    </p>
</div>
<div class="catalog-page-sort">
    <div class="catalog-page-sort-icon">
        <img src="{{ asset('front-assets/img/icons/sort.svg') }}" alt="Sort">
    </div>
    <div class="catalog-page-sort-item">
        <label for="sorting">{{ ShowLabelById(245) }}:</label>
        <select name="sorting" class="change-sorting" id="sorting">
            <option value="" {{ !$sorting ? 'selected' : '' }}>{{ ShowLabelById(246) }}</option>
            <option value="price_asc" {{ $sorting && $sorting == 'price_asc' ? 'selected' : '' }}>{{ ShowLabelById(247) }}</option>
            <option value="price_desc" {{ $sorting && $sorting == 'price_desc' ? 'selected' : '' }}>{{ ShowLabelById(248) }}</option>
        </select>
    </div>
    <div class="catalog-page-sort-item">
        <label for="goods-per-page">{{ ShowLabelById(249) }}:</label>
        <select name="goods_per_page" class="change-goods-per-page" id="goods-per-page">
            <option value="20" {{ $count_per_page == 20 ? 'selected' : '' }}>20</option>
            <option value="40" {{ $count_per_page == 40 ? 'selected' : '' }}>40</option>
            <option value="60" {{ $count_per_page == 60 ? 'selected' : '' }}>60</option>
        </select>
    </div>
</div>
