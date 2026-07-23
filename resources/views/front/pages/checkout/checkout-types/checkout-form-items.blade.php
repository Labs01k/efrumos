<div class="basket-account-title">{{ ShowLabelById(213) }}</div>
<div class="form-row form-row-4">
    <div class="form-item">
        <label for="checkout-last-name">{{ ShowLabelById(35) }}*</label>
        <input type="text" id="checkout-last-name" name="last_name"
               value="{{ request()->input('type') != 'without' && $global_user ? $global_user->last_name : '' }}">
    </div>
    <div class="form-item">
        <label for="checkout-name">{{ ShowLabelById(36) }}*</label>
        <input type="text" id="checkout-name" name="name"
               value="{{ request()->input('type') != 'without' && $global_user ? $global_user->name : '' }}">
    </div>
    <div class="form-item">
        <label for="checkout-phone">{{ ShowLabelById(41) }}*</label>
        <input type="number" id="checkout-phone" name="phone"
               value="{{ request()->input('type') != 'without' && $global_user ? $global_user->phone : '' }}">
    </div>
    <div class="form-item">
        <label for="checkout-email">{{ ShowLabelById(34) }}</label>
        <input type="email" id="checkout-email" name="email"
               value="{{ request()->input('type') != 'without' && $global_user ? $global_user->email : '' }}" {{ $global_user ? 'readonly' : '' }}>
    </div>
</div>
<div class="basket-account-title">{{ ShowLabelById(212) }}</div>
<div class="form-item">
    <label for="delivery_method" class="sr-only">{{ ShowLabelById(208) }}</label>
    <select name="delivery_method" id="delivery_method" class="change-delivery-method" data-total-price="{{ $total_price ?? 0 }}">
        <option value="delivery">{{ ShowLabelById(208) }}</option>
        <option value="pickup">{{ ShowLabelById(209) }}</option>
        {{--<option value="nova_courier">{{ ShowLabelById(210) }}</option>
        <option value="nova_terminal">{{ ShowLabelById(211) }}</option>--}}
    </select>
</div>

<div class="basket-info pickup d-none">
    {!! showSettingBodyByAlias('delivery-pickup-text') !!}
</div>

<div class="basket-info nova-courier d-none">
    <p>
        <img src="{{ asset('front-assets/img/icons/nova.png') }}" alt="Nova">
    </p>
    {!! showSettingBodyByAlias('delivery-nova-courier-text') !!}
</div>

<div class="basket-info nova-terminal d-none">
    <p>
        <img src="{{ asset('front-assets/img/icons/nova.png') }}" alt="Nova">
    </p>
    {!! showSettingBodyByAlias('delivery-nova-terminal-text') !!}
</div>

<div class="basket-account-address delivery-address">
    @if(!empty($districts) && count($districts))
        <div class="form-item basket-account-address--district">
            <label for="checkout-district-id" class="">{{ ShowLabelById(214) }}*</label>
            <select name="district_id" id="checkout-district-id" @if($total_price < config('custom.front.until_free_delivery'))class="select-district"
                    data-total-price="{{ $total_price ?? 0 }}"@endif>
                <option value="">{{ ShowLabelById(45) }}</option>
                @foreach($districts as $one_district)
                    <option value="{{ $one_district->id ?? '' }}" {{ request()->input('type') != 'without' && $global_user && $global_user->district_id == $one_district->id ? 'selected' : '' }}>{{ $one_district->name ?? '' }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="form-item basket-account-address--city">
        <label for="checkout-city">{{ ShowLabelById(215) }}*</label>
        <input type="text" id="checkout-city" name="city" placeholder="{{ ShowLabelById(216) }}" value="{{ request()->input('type') != 'without' && $global_user ? $global_user->city : '' }}">
    </div>
    <div class="form-item basket-account-address--address">
        <label for="checkout-address">{{ ShowLabelById(146) }}*</label>
        <input type="text" id="checkout-address" name="address"
               placeholder="{{ ShowLabelById(217) }}" value="{{ request()->input('type') != 'without' && $global_user ? $global_user->address : '' }}">
    </div>
</div>

<div class="basket-delivery-info show-other-currency d-none">
    {!! showSettingBodyByAlias('text-currency-transnistria') !!}
</div>

<div class="basket-account-title">{{ ShowLabelById(218) }}</div>
<div class="form-item">
    <label for="pay_method" class="sr-only">{{ ShowLabelById(76) }}</label>
    <select name="pay_method" id="pay_method">
        <option value="cash">{{ ShowLabelById(76) }}</option>
    </select>
</div>
<div class="form-item">
    <label for="already-comment" class="">{{ ShowLabelById(219) }}</label>
    <textarea name="comment" id="already-comment"
              placeholder="{{ ShowLabelById(220) }}"></textarea>
</div>
<div class="google-policies">
    <p>{!! ShowLabelById(27) !!}</p>
</div>
<p class="aggreement mt-1">
    <label>
        <input id="checkout-agree" name="agree" type="checkbox">
        <span class="aggreement-checkbox"></span>
        {!! ShowLabelById(28) !!}
    </label>
</p>

<div class="captcha">
    <input type="hidden" name="g-recaptcha-response" id="recaptcha-order-new">
</div>
