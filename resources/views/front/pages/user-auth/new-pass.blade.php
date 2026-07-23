@if(!is_null($recovery_user))
    <div class="common-modal reset-modal new-password active">
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
                        <p>{{ ShowLabelById(134) }}</p>
                    </div>
                    <div class="login-modal-form">
                        <form method="POST" action="{{ route('ajax-new-password') }}" id="new-password">

                            @csrf
                            <input type="hidden" name="hash" value="{{ $hash ?? '' }}">

                            <div class="form-item">
                                <label for="password">{{ ShowLabelById(100) }} *</label>
                                <input type="password" id="password" name="password">
                            </div>

                            <div class="form-item">
                                <label for="password_confirmation">{{ ShowLabelById(101) }} *</label>
                                <input type="password" id="password_confirmation" name="password_confirmation">
                            </div>

                            <div class="form-submit">
                                <button type="submit" class="button button--black prevent-repeated-click-new-pass" onclick="saveForm(this)"
                                        data-form-id="new-password">{{ ShowLabelById(133) }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
