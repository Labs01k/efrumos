@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                @if($parent_menu->itemByLang)
                    {{ Breadcrumbs::render($parent_menu->alias, $parent_menu) }}
                @endif
            </div>
        </div>

        <div class="section pt-0 contacts">
            <div class="container">
                <div class="contacts-inner">
                    <div class="contacts-form">
                        <form method="POST" action="{{ route('ajax-feedback') }}" id="contacts-form"
                              enctype="multipart/form-data">

                            @csrf

                            <div class="contacts-title">{{ ShowLabelById(136) }}</div>
                            <div class="contacts-desc">
                                <p>{{ ShowLabelById(137) }}</p>
                            </div>
                            <div class="form-item">
                                <label for="contacts-name">{{ ShowLabelById(36) }}*</label>
                                <input type="text" id="contacts-name" name="name">
                            </div>
                            <div class="form-item">
                                <label for="contacts-email">{{ ShowLabelById(34) }}*</label>
                                <input type="email" id="contacts-email" name="email">
                            </div>
                            <div class="form-item">
                                <label for="contacts-phone">{{ ShowLabelById(41) }}*</label>
                                <input type="number" id="contacts-phone" name="phone">
                            </div>
                            <div class="form-item">
                                <label for="contacts-comment">{{ ShowLabelById(138) }}</label>
                                <textarea id="contacts-comment" name="comment"></textarea>
                            </div>

                            <div class="google-policies">
                                <p>{!! ShowLabelById(27) !!}</p>
                            </div>

                            <p class="aggreement mt-1">
                                <label>
                                    <input id="contacts-agree" name="agree" type="checkbox">
                                    <span class="aggreement-checkbox"></span>
                                    {!! ShowLabelById(28) !!}
                                </label>
                            </p>

                            <div class="captcha">
                                <input type="hidden" name="g-recaptcha-response" id="recaptcha-contacts">
                            </div>

                            <div class="form-submit">
                                <button type="submit" class="button button--black prevent-repeated-click" onclick="saveForm(this)"
                                        data-form-id="contacts-form">{{ ShowLabelById(139) }}</button>
                            </div>
                        </form>
                    </div>
                    <div class="contacts-info">
                        <div class="contacts-title">{{ ShowLabelById(46) }}</div>
                        <div class="contacts-info-section">
                            <div class="contacts-info-text">
                                @if(showSettingBodyByAlias('main-cod-fiscal'))
                                    <div class="contacts-info-row">
                                        <p>{{ ShowLabelById(142) }}:</p>
                                        <p>{{ showSettingBodyByAlias('main-cod-fiscal') }}</p>
                                    </div>
                                @endif
                                @if(showSettingBodyByAlias('main-address'))
                                    <div class="contacts-info-row">
                                        <p>{{ ShowLabelById(143) }}:</p>
                                        <p>{{ showSettingBodyByAlias('main-address') }}</p>
                                    </div>
                                @endif
                                @if(showSettingBodyByAlias('main-bank'))
                                    <div class="contacts-info-row">
                                        <p>{{ ShowLabelById(144) }}:</p>
                                        <p>{{ showSettingBodyByAlias('main-bank') }}</p>
                                    </div>
                                @endif
                                @if(showSettingBodyByAlias('main-capital-social'))
                                    <div class="contacts-info-row">
                                        <p>{{ ShowLabelById(145) }}:</p>
                                        <p>{{ showSettingBodyByAlias('main-capital-social') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="contacts-info-section">
                            <div class="contacts-info-title">{{ ShowLabelById(116) }}</div>
                            <div class="contacts-info-text">
                                @if(showSettingBodyByAlias('main-email'))
                                    <div class="contacts-info-row">
                                        <p>{{ ShowLabelById(34) }}:</p>
                                        <p>{{ showSettingBodyByAlias('main-email') }}</p>
                                    </div>
                                @endif
                                @if(showSettingBodyByAlias('main-phone'))
                                    <div class="contacts-info-row">
                                        <p>{{ ShowLabelById(41) }}:</p>
                                        <p>{{ showSettingBodyByAlias('main-phone') }}</p>
                                    </div>
                                @endif
                                @if(showSettingBodyByAlias('main-address'))
                                    <div class="contacts-info-row">
                                        <p>{{ ShowLabelById(146) }}:</p>
                                        <p>{{ showSettingBodyByAlias('main-address') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="contacts-info-section">
                            <div class="contacts-info-title">{{ ShowLabelById(140) }}</div>
                            <div class="contacts-info-text">
                                <div class="stores-tab-list">
                                    <ul>
                                        <li>
                                            <div>
                                                <svg>
                                                    <use
                                                        xlink:href="{{ asset('front-assets/svg/sprite.svg#clock') }}"></use>
                                                </svg>
                                            </div>
                                            <div>{{ ShowLabelById(141) }}</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop
