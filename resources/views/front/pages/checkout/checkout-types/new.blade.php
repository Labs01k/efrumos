<div class="basket-account-form">
    <form method="POST" action="{{ route('ajax-new-order') }}" id="new-order"
          enctype="multipart/form-data">

        @csrf
        <input type="hidden" name="order_type" value="new">

        @include('front.pages.checkout.checkout-types.checkout-form-items')

        <div class="form-submit">
            <button type="submit" class="button button--black prevent-repeated-click" onclick="newOrder(this)"
                    data-form-id="new-order">{{ ShowLabelById(178) }}</button>
        </div>
    </form>
</div>
