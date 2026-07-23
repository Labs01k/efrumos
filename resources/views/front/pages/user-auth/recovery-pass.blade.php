<div class="common-modal reset-modal">
    <div class="common-modal-bg"></div>
    <div class="common-modal-wrapper">
        <button type="button" class="common-modal-close">
            <svg>
                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
            </svg>
        </button>
        <div class="common-modal-inner">
            <div class="login-modal-inner">
                <div class="login-modal-title h1">{{ ShowLabelById(133) }}</div>
                <div class="login-modal-text">
                    <p>{{ ShowLabelById(132) }}</p>
                </div>
                <div class="login-modal-form">
                    <form method="POST" action="{{ route('ajax-restore-password') }}" id="restore-password">

                        @csrf

                        <div class="form-item">
                            <label for="email-recovery">{{ ShowLabelById(34) }} *</label>
                            <input type="email" id="email-recovery" name="email">
                        </div>

                        <div class="google-policies">
                            <p>{!! ShowLabelById(27) !!}</p>
                        </div>
                        <p class="aggreement mt-1">
                            <label>
                                <input id="agree" name="agree" type="checkbox">
                                <span class="aggreement-checkbox"></span>
                                {!! ShowLabelById(28) !!}
                            </label>
                        </p>

                        <div class="captcha">
                            <input type="hidden" name="g-recaptcha-response" id="recaptcha-restore-password">
                        </div>

                        <div class="form-submit">
                            <button type="submit" class="button button--black prevent-repeated-click-recovery" onclick="saveForm(this)" data-form-id="restore-password">{{ ShowLabelById(130) }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
