<div class="mobile-menu">
    <div class="mobile-menu-head">
        <button type="button" class="submenu-back">
            <svg>
                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#back') }}"></use>
            </svg>
            <span>{{ ShowLabelById(111) }}</span>
        </button>
        <button type="button" class="mobile-menu-close">
            <svg>
                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
            </svg>
        </button>
    </div>
    <div class="mobile-menu-content">

        <div class="mobile-menu-catalog">
            @if($header_goods_subjects && $header_goods_subjects->children->isNotEmpty())
                <div class="catalog-inner">
                    <div class="catalog-list">
                        @include('front.templates.header-catalog-menu')
                    </div>
                </div>
            @else
                <div class="catalog-inner text-center">
                    <span class="">{{ ShowLabelById(108) }}</span>
                </div>
            @endif
        </div>

        @if($top_header_menu && $top_header_menu->children->isNotEmpty())
            <div class="mobile-menu-links">
                <ul>
                    @foreach($top_header_menu->children as $one_menu_item)
                        <li{{ request()->routeIs($one_menu_item->alias) ? ' class=active' : '' }}>
                            <a href="{{ $one_menu_item->page_type == 'link' ? $one_menu_item->itemByLang->link : route('menu', $one_menu_item->alias) }}">{{ $one_menu_item->itemByLang->name ?? '' }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mobile-menu-footer">
            <div class="mobile-menu-row">
                @if(showSettingBodyByAlias('main-phone'))
                    <div class="main-header-phone">
                        <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', showSettingBodyByAlias('main-phone')) }}">
                            <svg>
                                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#phone') }}"></use>
                            </svg>
                            <span>{{ showSettingBodyByAlias('main-phone') }}</span>
                        </a>
                    </div>
                @endif
                @if(!empty($lang_list) && count($lang_list))
                    <div class="main-header-langs">
                        <ul>
                            @foreach($lang_list as $one_lang)
                                <li {{ $one_lang == LANG ? 'class=active' : '' }}>
                                    <a href="{{ count(request()->segments()) > 1 ? str_replace('/'.LANG. '/', '/'.$one_lang.'/', request()->fullUrl()) : (count(request()->segments()) == 1 ? str_replace('/'.LANG, '/'.$one_lang, request()->fullUrl()) : url($one_lang)) }}">{{ ucfirst($one_lang) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            @if(!empty($social_media) && count($social_media))
                <div class="main-header-socials">
                    <ul>
                        @foreach($social_media as $one_item)
                            @include('front.templates.social-links', ['icon_type' => 'svg'])
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="main-header-schedule">{!! ShowLabelById(113) !!}</div>
        </div>
    </div>
</div>
