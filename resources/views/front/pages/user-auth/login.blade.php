<div class="common-modal login-modal">
    <div class="common-modal-bg"></div>
    <div class="common-modal-wrapper">
        <button type="button" class="common-modal-close">
            <svg>
                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
            </svg>
        </button>
        <div class="common-modal-inner">
            <div class="login-modal-inner">
                <div class="login-modal-title h1">{{ ShowLabelById(168) }}</div>
                <div class="basket-account-socials">
                    <p>{{ ShowLabelById(169) }}</p>
                    <ul>
                        <li>
                            <a href="{{ route('login-facebook') }}" class="social-facebook">{{ ShowLabelById(31) }}</a>
                        </li>
                        <li>
                            <a href="{{ route('login-google') }}" class="social-google">{{ ShowLabelById(32) }}</a>
                        </li>
                    </ul>
                </div>
                <div class="login-modal-text">
                    <p>{{ ShowLabelById(170) }}</p>
                </div>
                <div class="login-modal-form">
                    <form action="{{ route('ajax-login-user') }}" method="POST" id="login-user">

                        @csrf
                        <input type="hidden" name="current_url" value="{{ url()->current() }}">

                        <div class="form-item">
                            <label for="login-email">{{ ShowLabelById(34) }}</label>
                            <input type="email" id="login-email" name="email">
                        </div>
                        <div class="form-item">
                            <label for="login-password">{{ ShowLabelById(42) }}</label>
                            <input type="password" id="login-password" name="password">
                        </div>
                        <div class="login-modal-info">
                            <p class="aggreement mt-0">
                                <label>
                                    <input id="remember" name="remember" type="checkbox">
                                    <span class="aggreement-checkbox"></span>
                                    {{ ShowLabelById(171) }}
                                </label>
                            </p>
                            <div class="login-modal-link">
                                <a href="javascript:;" class="open-reset-modal">{{ ShowLabelById(172) }}</a>
                            </div>
                        </div>
                        <div class="form-submit">
                            <button type="submit" class="button button--black" onclick="saveForm(this)"
                                    data-form-id="login-user">{{ ShowLabelById(175) }}</button>
                        </div>
                    </form>
                </div>
                <div class="login-modal-footer">
                    <p>{{ ShowLabelById(174) }} <a href="{{ route('register') }}">{{ ShowLabelById(173) }}</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
