<div class="common-modal one-click">
    <div class="common-modal-bg"></div>
    <div class="common-modal-wrapper">
        <button type="button" class="common-modal-close">
            <svg>
                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
            </svg>
        </button>
        <div class="common-modal-inner">
            <div class="one-click-title">{{ ShowLabelById(250) }}</div>
            <div class="one-click-desc">
                <p>{{ $goods_item->itemByLang->name ?? '' }}</p>
            </div>
            <div class="one-click-form">
                <form method="POST" action="{{ route('ajax-new-fast-order') }}" id="new-fast-order"
                      enctype="multipart/form-data">

                    @csrf
                    <input type="hidden" name="goods_item_id" value="{{ $goods_item->id ?? '' }}">
                    <input type="hidden" name="current_url" value="{{ url()->current() }}">

                    <div class="form-row">
                        <div class="form-item">
                            <label for="fast-order-last-name">{{ ShowLabelById(35) }}</label>
                            <input type="text" id="fast-order-last-name" name="last_name" value="{{ $global_user ? $global_user->last_name : '' }}">
                        </div>
                        <div class="form-item">
                            <label for="fast-order-name">{{ ShowLabelById(36) }}</label>
                            <input type="text" id="fast-order-name" name="name" value="{{ $global_user ? $global_user->name : '' }}">
                        </div>
                    </div>
                    <div class="form-item">
                        <label for="fast-order-phone">{{ ShowLabelById(41) }}</label>
                        <input type="number" id="fast-order-phone" name="phone" value="{{ $global_user ? $global_user->phone : '' }}">
                    </div>

                    <div class="google-policies">
                        <p>{!! ShowLabelById(27) !!}</p>
                    </div>
                    <p class="aggreement">
                        <label>
                            <input id="fast-order-agree" name="agree" type="checkbox">
                            <span class="aggreement-checkbox"></span>
                            {!! ShowLabelById(28) !!}
                        </label>
                    </p>

                    <div class="captcha">
                        <input type="hidden" name="g-recaptcha-response" id="recaptcha-order-new">
                    </div>

                    <div class="form-submit">
                        <button type="submit" class="button button--black prevent-repeated-click" onclick="newOrder(this)"
                                data-form-id="new-fast-order">{{ ShowLabelById(178) }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="common-modal succes-fast-order">
    <div class="common-modal-bg"></div>
    <div class="common-modal-wrapper">
        <button type="button" class="common-modal-close">
            <svg>
                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#close') }}"></use>
            </svg>
        </button>
        <div class="common-modal-inner text-center">
            <div class="basket-end-icon">
                <img src="{{ asset('front-assets/img/icons/basket-success.svg') }}" alt="Success">
            </div>
            <div class="one-click-title">{{ ShowLabelById(273) }}</div>
            <div class="one-click-desc">
                <p>{{ ShowLabelById(251) }}</p>
            </div>
        </div>
    </div>
</div>
