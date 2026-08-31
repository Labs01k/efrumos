<footer class="main-footer">
    <div class="main-footer-top">
        <div class="container">
            <div class="main-footer-top-inner">
                <div class="main-footer-info">{{ ShowLabelById(164) }}</div>
                <div class="main-footer-form">
                    <form method="POST" action="{{ route('ajax-subscribers') }}" id="subscribers"
                          enctype="multipart/form-data">

                        @csrf

                        <label for="subscribers-email" class="sr-only">{{ ShowLabelById(165) }} ...</label>
                        <div class="footer-form-row position-relative">
                            <input type="email" id="subscribers-email" name="subscribers_email"
                                   placeholder="{{ ShowLabelById(165) }} ...">

                            <div class="captcha">
                                <input type="hidden" name="g-recaptcha-response" id="recaptcha-subscribers">
                            </div>

                            <button type="submit" class="button prevent-repeated-click-subscribers" onclick="saveForm(this)"
                                    data-form-id="subscribers">{{ ShowLabelById(166) }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="main-footer-middle">
        <div class="container">
            <div class="main-footer-middle-inner">
                <div class="main-footer-col">
                    <div class="main-footer-head">
                        <div class="main-footer-logo">
                            <a href="{{ route('/') }}">
                                <img src="{{ asset('front-assets/img/logo/logo.svg') }}" alt="">
                            </a>
                        </div>
                        <div class="main-footer-phone">
                            <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', showSettingBodyByAlias('main-phone')) }}">
                                <svg>
                                    <use xlink:href="{{ asset('front-assets/svg/sprite.svg#phone') }}"></use>
                                </svg>
                                <span>{{ showSettingBodyByAlias('main-phone') }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="main-footer-payment d-none d-xl-block">
                        <p>{{ ShowLabelById(114) }}:</p>
                        <ul>
                            <li>
                                <img src="{{ asset('front-assets/img/icons/visa.png') }}" alt="Visa">
                            </li>
                            <li>
                                <img src="{{ asset('front-assets/img/icons/mastercard.png') }}" alt="Mastercard">
                            </li>
                            {{--<li>
                                <img src="{{ asset('front-assets/img/icons/paypal.png') }}" alt="Paypal">
                            </li>
                            <li>
                                <img src="{{ asset('front-assets/img/icons/discover.png') }}" alt="Discover">
                            </li>--}}
                        </ul>
                    </div>
                </div>
                @if($header_goods_subjects)
                    <div class="main-footer-col">
                        <div class="main-footer-title">{{ $header_goods_subjects->itemByLang->name ?? '' }}</div>
                        <div class="main-footer-list">
                            <ul>
                                @foreach($header_goods_subjects->children as $one_goods_subject_l1)
                                    <li>
                                        <a href="{{ route('category', $one_goods_subject_l1->alias) }}">{{ $one_goods_subject_l1->itemByLang->name ?? '' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                @if($footer_menu && $footer_menu->children->isNotEmpty())
                    <div class="main-footer-col">
                        <div class="main-footer-title">{{ ShowLabelById(106) }}</div>
                        <div class="main-footer-list">
                            <ul>
                                @foreach($footer_menu->children as $one_menu_item)
                                    <li>
                                        <a href="{{ $one_menu_item->page_type == 'link' ? $one_menu_item->itemByLang->link : route('menu', $one_menu_item->alias) }}">{{ $one_menu_item->itemByLang->name ?? '' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                @if($footer_menu_info && $footer_menu_info->children->isNotEmpty())
                    <div class="main-footer-col">
                        <div class="main-footer-title">{{ ShowLabelById(115) }}</div>
                        <div class="main-footer-list">
                            <ul>
                                @foreach($footer_menu_info->children as $one_menu_item)
                                    <li>
                                        <a href="{{ $one_menu_item->page_type == 'link' ? $one_menu_item->itemByLang->link : route('menu', $one_menu_item->alias) }}">{{ $one_menu_item->itemByLang->name ?? '' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <div class="main-footer-col">
                    <div class="main-footer-title"><a href="{{ route('menu', 'contacts') }}" class="text-decoration-none">{{ ShowLabelById(116) }}</a></div>
                    <div class="main-footer-list">
                        <ul>
                            @if(showSettingBodyByAlias('main-address'))
                                <li>{{ showSettingBodyByAlias('main-address') }}</li>
                            @endif
                            @if(showSettingBodyByAlias('main-phone'))
                                <li>
                                    <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', showSettingBodyByAlias('main-phone')) }}">{{ showSettingBodyByAlias('main-phone') }}</a>
                                </li>
                            @endif
                            @if(showSettingBodyByAlias('main-email'))
                                <li>
                                    <a href="mailto:{{ showSettingBodyByAlias('main-email') }}">{{ showSettingBodyByAlias('main-email') }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="main-footer-payment d-xl-none">
                        <p>{{ ShowLabelById(114) }}:</p>
                        <ul>
                            <li>
                                <img src="{{ asset('front-assets/img/icons/visa.png') }}" alt="Visa">
                            </li>
                            <li>
                                <img src="{{ asset('front-assets/img/icons/mastercard.png') }}" alt="Mastercard">
                            </li>
                            {{--<li>
                                <img src="{{ asset('front-assets/img/icons/paypal.png') }}" alt="Paypal">
                            </li>
                            <li>
                                <img src="{{ asset('front-assets/img/icons/discover.png') }}" alt="Discover">
                            </li>--}}
                        </ul>
                    </div>
                    @if(!empty($social_media) && count($social_media))
                        <div class="main-footer-socials">
                            <ul>
                                @foreach($social_media as $one_item)
                                    @include('front.templates.social-links', ['icon_type' => 'img'])
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {{--
                    <div class="main-footer-link">
                        <a href="javascript:;" class="button button--inversed">{{ ShowLabelById(118) }}</a>
                    </div>
                    --}}
                </div>
            </div>
        </div>
    </div>
    <div class="main-footer-bottom">
        <div class="container">
            <div class="main-footer-bottom-inner">
                @if(showSettingBodyByAlias('footer-copyright'))
                    <div class="copyright">
                        <p>{{ str_replace('{year}', \Carbon\Carbon::now()->format('Y'), showSettingBodyByAlias('footer-copyright')) }}</p>
                    </div>
                @endif
                <div class="webit">
                    <a href="{{ LANG == 'ro' ? 'https://www.webit.md/ro/website-development-moldova.html' : 'https://www.webit.md/'}}" target="_blank" aria-label="{{ LANG == 'ro' ? 'Creare site' : 'Разработка сайта' }}" title="{{ LANG == 'ro' ? 'Creare site' : 'Разработка сайта' }}"></a>
                </div>
            </div>
        </div>
    </div>
</footer>

<div id="fixed-overlay"></div>

<script src="{{ asset('front-assets/js/libs.min.js?v=').config('custom.front.js_version') }}"></script>
<script src="{{ asset('front-assets/js/main.js?v=').config('custom.front.js_version') }}"></script>
<script src="{{ asset('front-assets/js/notiflix-3.2.6.min.js') }}"></script>
<script src="{{ asset('front-assets/js/recaptcha.js') }}"></script>
<script src="{{ asset('front-assets/js/ajax-scripts.js?v=').config('custom.front.js_version') }}"></script>
<script src="{{ asset('front-assets/js/product-card.js?v=').config('custom.front.js_version') }}"></script>
<script src="https://www.google.com/recaptcha/api.js?render={{ env('RE_CAP_SECRET') }}"></script>


<script>
    getRecaptcha('/contacts', 'recaptcha-contacts');
    getRecaptcha('/', 'recaptcha-register');
    getRecaptcha('/', 'recaptcha-restore-password');
    getRecaptcha('/cart', 'recaptcha-order-new');
    getRecaptcha('/cart', 'recaptcha-order-already');
    getRecaptcha('/cart', 'recaptcha-order-without');
    getRecaptcha('/', 'recaptcha-form-goods-review');
    getRecaptcha('/', 'recaptcha-subscribers');
</script>

{{--Modal add to cart--}}
<div class="common-modal add-to-cart">
    <div class="common-modal-bg"></div>
    <div class="common-modal-wrapper">
        <button type="button" class="common-modal-close">
            <svg>
                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
            </svg>
        </button>
        <div class="common-modal-inner render-modal-add-to-basket"></div>
    </div>
</div>

{{--Modal quick view goods--}}
<div class="common-modal quick-view">
    <div class="common-modal-bg"></div>
    <div class="common-modal-wrapper">
        <button type="button" class="common-modal-close">
            <svg>
                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
            </svg>
        </button>
        <div class="common-modal-inner render-modal-quick-view">

        </div>
    </div>
</div>

{{--Show errors--}}
<div class="popup-error" style="display: none;">
    <div class="popup-error-close">
        <svg>
            <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
        </svg>
    </div>
    <div class="common-text">
        <p><strong>{{ ShowLabelById(277) }}:</strong></p>
        <ul></ul>
    </div>
</div>

{{--Verification message--}}
@if(session()->has('verification-message'))
    <script>
        Notiflix.Notify.success('{{ session('verification-message') }}', {
                position: 'center-top',
                timeout: 6000,
            }
        );
    </script>
@endif

<a href="javascript:;" class="to-top">
    <svg>
        <use xlink:href="{{ asset('front-assets/svg/sprite.svg#to-top') }}"></use>
    </svg>
</a>

@include('front.templates.before-body-end', ['cookie_settings' => $cookie])
