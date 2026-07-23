@include('front.templates.after-body-start'/*, ['cookie_settings' => $cookie]*/)

@if(!ifUserSessionExists())
    @include('front.pages.user-auth.login')
    @include('front.pages.user-auth.recovery-pass')
    @include('front.pages.user-auth.new-pass')
@Endif

@include('front.templates.header-mobile-menu')

<div class="common-modal basket-modal render-right-header-basket">
    @include('front.templates.header-basket-items')
</div>

<header class="main-header">
    @if($header_top_banner)
        <a {{ $header_top_banner->itemByLang->link ? 'href='.$header_top_banner->itemByLang->link : '' }} class="main-header-top d-block">
            @if($header_top_banner->oImage && $header_top_banner->oImage->img && file_exists('upfiles/banners/' . $header_top_banner->oImage->img))
                <div class="header-top-bg">
                    <picture>
                        @if($header_top_banner->oImage && $header_top_banner->oImageDesc->img && file_exists('upfiles/banners/' . $header_top_banner->oImageDesc->img))
                            @if(isMobile())
                                <source
                                    srcset="{{ asset('upfiles/banners/'. $header_top_banner->oImageDesc->img) }}"
                                    media="(max-width: 991px)">
                            @endif
                        @endif
                        <img class="full-width" src="{{ asset('upfiles/banners/'. $header_top_banner->oImage->img) }}"
                             alt="{{ $header_top_banner->itemByLang->short_descr ?? '' }}">
                    </picture>
                </div>
            @endif
            <div class="header-top-content">
                <div class="container">
                    <div class="main-header-top-inner">
                        {{--<div class="header-top-discount">{!! $header_top_banner->percent ?? '' !!}</div>--}}
                        {{-- <div class="header-top-text">{{ $header_top_banner->itemByLang->short_descr ?? '' }}</div>--}}
                    </div>
                </div>
            </div>
        </a>
    @endif
    <div class="main-header-middle">
        <div class="container">
            <div class="main-header-middle-inner">
                <div class="main-header-info">{{ ShowLabelById(112) }}</div>
                <div class="main-header-middle-right">
                    <div class="main-header-schedule">{!! ShowLabelById(113) !!}</div>
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
                @if(showSettingBodyByAlias('main-phone'))
                    <div class="main-header-phone d-lg-none">
                        <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', showSettingBodyByAlias('main-phone')) }}">
                            <svg>
                                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#phone') }}"></use>
                            </svg>
                            <span>{{ showSettingBodyByAlias('main-phone') }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="main-header-bottom">
        <div class="container">
            <div class="main-header-bottom-inner">
                <div class="main-header-logo">
                    <a href="{{ route('/') }}">
                        <img src="{{ asset('front-assets/img/logo/logo.svg') }}" alt="Efrumos logo">
                    </a>
                </div>
                <div class="main-header-search d-none d-large-block">
                    <form action="{{ route('catalog-product') }}" method="GET" class="ajax-search-form">
                        <label for="search" class="sr-only">{{ ShowLabelById(109) }} ...</label>
                        <input type="text" name="s" id="search" value="{{ $header_search ?? '' }}"
                               placeholder="{{ ShowLabelById(109) }} ..." autocomplete="off">
                        <button type="submit" class="main-header-search-submit">
                            <svg>
                                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#search') }}"></use>
                            </svg>
                        </button>
                    </form>
                    @include('front.templates.header-search')
                </div>
                <div class="header-search-bg"></div>


                @if(!empty($social_media) && count($social_media))
                    <div class="main-header-socials">
                        <p>{{ ShowLabelById(110) }}:</p>
                        <ul>
                            @foreach($social_media as $one_item)
                                @include('front.templates.social-links', ['icon_type' => 'svg'])
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(showSettingBodyByAlias('main-phone'))
                    <div class="main-header-phone d-none d-lg-block">
                        <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', showSettingBodyByAlias('main-phone')) }}">
                            <svg>
                                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#phone') }}"></use>
                            </svg>
                            <span>{{ showSettingBodyByAlias('main-phone') }}</span>
                        </a>
                    </div>
                @endif
                <div class="main-header-links">
                    <ul>
                        <li>
                            @if(ifUserSessionExists())
                                <a href="{{ route('cabinet-profile') }}">
                                    <svg>
                                        <use
                                            xlink:href="{{ asset('front-assets/svg/sprite.svg#login-success') }}"></use>
                                    </svg>
                                </a>
                            @else
                                <a href="javascript:;" class="open-login-modal">
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#account') }}"></use>
                                    </svg>
                                </a>
                            @endif
                        </li>
                        @if(ifUserSessionExists())
                            <li>
                                <a href="{{ route('cabinet-wish') }}">
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart') }}"></use>
                                    </svg>
                                    <span
                                        class="header-wish-count" {{ !$wish_count ? 'style=display:none' : '' }}>{{ $wish_count ?? 0 }}</span>
                                </a>
                            </li>
                        @else
                            <a href="javascript:;" class="open-login-modal">
                                <svg>
                                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#heart') }}"></use>
                                </svg>
                            </a>
                        @endif
                        <li>
                            @if(request()->segment(2) != 'cart' && request()->segment(2) != 'checkout')
                                <a href="javascript:;" class="open-basket-modal">
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#cart') }}"></use>
                                    </svg>
                                    <span
                                        class="header-basket-count" {{ !$basket_count ? 'style=display:none' : '' }}>{{ $basket_count ?? 0 }}</span>
                                </a>
                            @else
                                <a href="{{ route('cart') }}">
                                    <svg>
                                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#cart') }}"></use>
                                    </svg>
                                    <span
                                        class="header-basket-count" {{ !$basket_count ? 'style=display:none' : '' }}>{{ $basket_count ?? 0 }}</span>
                                </a>
                            @endif
                        </li>
                    </ul>
                </div>
                @if(!empty($lang_list) && count($lang_list))
                    <div class="mobile-lang">
                        <button type="button" class="mobile-lang-current">{{ ucfirst(LANG) }}</button>
                        <div class="mobile-lang-list">
                            <ul>
                                @foreach($lang_list as $one_lang)
                                    <li>
                                        @if($one_lang != LANG)
                                            <a href="{{ count(request()->segments()) > 1 ? str_replace('/'.LANG. '/', '/'.$one_lang.'/', request()->fullUrl()) : (count(request()->segments()) == 1 ? str_replace('/'.LANG, '/'.$one_lang, request()->fullUrl()) : url($one_lang)) }}">{{ ucfirst($one_lang) }}</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="main-header-nav">
        <div class="container">
            <div class="main-header-nav-inner">
                <button type="button" class="burger-menu">
                    <svg>
                        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#burger-2') }}"></use>
                    </svg>
                    <span>{{ ShowLabelById(106) }}</span>
                </button>
                <div class="main-header-catalog">
                    @if($header_goods_subjects && $header_goods_subjects->children->isNotEmpty())
                        <button type="button" class="header-catalog-btn">
                            <svg>
                                <svg>
                                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#burger') }}"></use>
                                </svg>
                            </svg>
                            <span>{{ ShowLabelById(107) }}</span>
                        </button>
                        <div class="catalog-inner">
                            <div class="catalog-list">
                                @include('front.templates.header-catalog-menu')
                            </div>
                        </div>
                    @else
                        <button type="button" class="header-catalog-btn">
                            <span>{{ ShowLabelById(108) }}</span>
                        </button>
                    @endif
                </div>
                @if($top_header_menu && $top_header_menu->children->isNotEmpty())
                    <nav>
                        <ul>
                            @foreach($top_header_menu->children as $one_menu_item)
                                <li{{ request()->routeIs($one_menu_item->alias) ? ' class=active' : '' }}>
                                    <a href="{{ $one_menu_item->page_type == 'link' ? $one_menu_item->itemByLang->link : route('menu', $one_menu_item->alias) }}">{{ $one_menu_item->itemByLang->name ?? '' }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif
                <div class="main-header-search d-block d-large-none">
                    <form action="{{ route('catalog-product') }}" method="GET" class="ajax-search-form">
                        <label for="search-mob" class="sr-only">{{ ShowLabelById(109) }} ...</label>
                        <input type="text" name="s" id="search-mob" value="{{ $header_search ?? '' }}"
                               placeholder="{{ ShowLabelById(109) }} ..." autocomplete="off">
                        <button type="submit" class="main-header-search-submit">
                            <svg>
                                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#search') }}"></use>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
