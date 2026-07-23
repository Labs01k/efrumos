<div class="basket-account-form">
    @if(!$global_user)
        <form action="{{ route('ajax-login-user') }}" method="POST" id="already-login-user">

            @csrf
            <input type="hidden" name="current_url" value="{{ url()->current() }}">

            <div class="form-row form-row-3 already-customer">
                <div class="form-item">
                    <label for="already-email">{{ ShowLabelById(34) }}</label>
                    <input type="email" id="already-email" name="email">
                </div>
                <div class="form-item">
                    <label for="already-email">{{ ShowLabelById(42) }}</label>
                    <input type="password" id="already-email" name="password">
                    <div class="basket-account-reset">
                        <a href="javascript:;" class="open-reset-modal">{{ ShowLabelById(172) }}</a>
                    </div>
                </div>
                <div class="form-item">
                    <button type="button" class="button button--black" onclick="saveForm(this)"
                            data-form-id="already-login-user">{{ ShowLabelById(176) }}
                    </button>
                </div>
            </div>

            <div class="basket-account-socials">
                <p>{{ ShowLabelById(177) }}:</p>
                <ul>
                    <li>
                        <a href="{{ route('login-facebook') }}" class="social-facebook">{{ ShowLabelById(31) }}</a>
                    </li>
                    <li>
                        <a href="{{ route('login-google') }}" class="social-google">{{ ShowLabelById(32) }}</a>
                    </li>
                </ul>
            </div>
        </form>
    @else
        <form method="POST" action="{{ route('ajax-new-order') }}" id="new-order"
              enctype="multipart/form-data">

            @csrf
            <input type="hidden" name="order_type" value="already">

            @include('front.pages.checkout.checkout-types.checkout-form-items')

            <div class="form-submit">
                <button type="submit" class="button button--black prevent-repeated-click" onclick="newOrder(this)"
                        data-form-id="new-order">{{ ShowLabelById(178) }}
                </button>
            </div>
        </form>
    @endif
</div>
