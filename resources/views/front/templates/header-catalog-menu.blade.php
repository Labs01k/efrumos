<ul>
    @foreach($header_goods_subjects->children as $one_goods_subject_l1)
        @if($one_goods_subject_l1->children->isNotEmpty())
            <li class="has-submenu">
                <div class="catalog-head">
                    <a href="{{ route('category', $one_goods_subject_l1->alias) }}" class="show-submenu-img-l1"
                       data-img-source="{{ $one_goods_subject_l1->img && file_exists('upfiles/goods-subject/m/' . showImg($one_goods_subject_l1->img)) ? asset('upfiles/goods-subject/m/' . showImg($one_goods_subject_l1->img)) : asset('front-assets/img/no-image-menu.png') }}">
                        <svg>
                            <use
                                xlink:href="{{ asset('front-assets/svg/sprite.svg?v=').config('custom.front.svg_version') }}#{{ $one_goods_subject_l1->icon_name }}"></use>
                        </svg>
                        <span>{{ $one_goods_subject_l1->itemByLang->name ?? '' }}</span>
                    </a>
                    <div class="catalog-arrow">
                        <svg>
                            <use
                                xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                        </svg>
                    </div>
                </div>
                <div class="submenu">
                    <div class="submenu-inner">
                        <div class="submenu-list">
                            <ul>
                                @foreach($one_goods_subject_l1->children as $one_goods_subject_l2)
                                    <li>
                                        <a href="{{ route('category', $one_goods_subject_l2->alias) }}">{{ $one_goods_subject_l2->itemByLang->name ?? '' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="submenu-content">
                            @if($one_goods_subject_l1->goodsSeoPages->isNotEmpty())
                                @foreach($one_goods_subject_l1->goodsSeoPages as $one_seo_category)
                                    <div class="submenu-group">
                                        <div class="submenu-title">
                                            <a href="{{ route('category-seo-page', $one_seo_category->alias) }}">{{ $one_seo_category->itemByLang->name ?? '' }}</a>
                                        </div>
                                        @if($one_seo_category->children->isNotEmpty())
                                            <div class="submenu-links">
                                                <ul>
                                                    @foreach($one_seo_category->children as $one_goods_page)
                                                        <li>
                                                            <a href="{{ route('category-seo-page', $one_goods_page->alias) }}">{{ $one_goods_page->itemByLang->name ?? '' }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                            @endif
                        </div>
                        <div class="submenu-img">
                            <a{{ $one_goods_subject_l1->itemByLang && $one_goods_subject_l1->itemByLang->link_banner ? ' href='.$one_goods_subject_l1->itemByLang->link_banner : '' }}>
                                <img
                                    src="{{ $one_goods_subject_l1->img && file_exists('upfiles/goods-subject/m/' . showImg($one_goods_subject_l1->img)) ? asset('upfiles/goods-subject/m/' . showImg($one_goods_subject_l1->img)) : asset('front-assets/img/no-image-menu.png') }}">
                            </a>
                        </div>
                    </div>
                </div>
            </li>
        @endif
    @endforeach
</ul>
