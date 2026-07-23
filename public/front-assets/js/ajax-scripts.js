//Notiflix 3.2.6 Init
Notiflix.Notify.init({
    width: '280px',
    position: 'center-top',
    distance: '10px',
    opacity: 1,
    borderRadius: '5px',
    rtl: false,
    //timeout: 3000,
    success: {
        background: '#32c682',
        textColor: '#fff',
    },
    failure: {
        background: '#ff5549',
        textColor: '#fff',
    }
});

Notiflix.Loading.init({
    svgColor: "#E47F9E"
});

Notiflix.Block.init({
    querySelectorLimit: 200,
    className: 'notiflix-block',
    position: 'absolute',
    zindex: 1000,
    backgroundColor: 'rgba(255,255,255,0.9)',
    rtl: false,
    fontFamily: 'Quicksand',
    cssAnimation: true,
    cssAnimationDuration: 300,
    svgColor: '#FFD500',
});



//feedback
function saveForm(e) {

    let form_id = $(e).data('form-id');
    $('#' + form_id).submit(function (event) {
        event.preventDefault();
    });

    let form = $('#' + form_id);
    // var serializedForm = $(form).find("select, textarea, input").serializeArray();

    let serializedForm = new FormData(form[0]);

    let timeout_success = 2000;
    let timeout_error = 6000;
    let show_notiflix = 1;
    let show_notiflix_loader = 0;
    let prevent_repeated = '';

    switch (form_id) {
        case 'register-user':
            timeout_success = 500;
            timeout_error = 6000;
            show_notiflix = 0;
            show_notiflix_loader = 1;
            prevent_repeated = '.prevent-repeated-click-register';
            break;
        case 'login-user':
            timeout_success = 2000;
            timeout_error = 6000;
            break;
        case 'restore-password':
            timeout_success = 4000;
            timeout_error = 6000;
            prevent_repeated = '.prevent-repeated-click-recovery';
            break;
        case 'new-password':
            timeout_success = 6000;
            timeout_error = 6000;
            prevent_repeated = '.prevent-repeated-click-new-pass';
            break;
        case 'check-promocod':
            timeout_success = 6000;
            timeout_error = 6000;
            prevent_repeated = '.prevent-repeated-click-promocod';
            break;
        case 'subscribers':
            prevent_repeated = '.prevent-repeated-click-subscribers';
            break;
        default:
            break;
    }

    $(prevent_repeated).prop('disabled', true);

    if (!$(form)) {
        return;
    }

    $.ajax({
        method: "POST",
        url: $(form).attr('action'),
        data: serializedForm,
        beforeSend: function () {
            if (show_notiflix_loader === 1)
                Notiflix.Loading.standard();
        },
        enctype: 'multipart/form-data',
        processData: false,  // Important!
        contentType: false,
        cache: false,
        success: function (response) {

            //Recaptcha reset
            getRecaptcha('/', 'recaptcha-register');
            getRecaptcha('/', 'recaptcha-restore-password');
            getRecaptcha('/', 'recaptcha-form-goods-review');
            getRecaptcha('/contacts', 'recaptcha-contacts');
            getRecaptcha('/', 'recaptcha-subscribers');

            //Remove error message
            form.find('label.error').remove();
            form.find('label.error-promocode').remove();
            form.find('label.error-subscribe').remove();
            form.find('label.error-inline').remove();


            if (response.status == true) {

                $('.error-input').removeClass('error-input');

                if (show_notiflix === 1) {
                    Notiflix.Notify.success(response.message, {
                            position: 'center-top',
                            plainText: false,
                            timeout: timeout_success
                        }
                    );
                }

                setTimeout(function () {
                    if (response.redirect != null) {
                        window.location.href = response.redirect;
                    }

                    if (response.hide_modal === 1) {
                        $('.login-modal').removeClass('active'); //new-password
                        $('.reset-modal').removeClass('active'); //recovery-password
                        $('#fixed-overlay').removeClass('active');
                    }

                }, timeout_success);

                if (response.hide_modal === 1) {
                    $('.new-password').removeClass('active'); //recovery-password
                }

                //remove inputs values after send message
                if (response.remove_inputs_value === 1) {
                    form.find('input[type=text],input[type=email], input[type=password], input[type=date], input[type=number], textarea, select').val('');
                    $('#agree').prop('checked', false);
                    $('#contacts-agree').prop('checked', false);
                }

            } else if (response.status === 'warning') {

                Notiflix.Notify.warning(response.message, {
                        position: 'center-top',
                        plainText: false,
                        messageMaxLength: 500,
                        timeout: 180000,
                    }
                );

                $(prevent_repeated).prop('disabled', false);

            } else {
                if (response.messages != null) {
                    $('.prevent-repeated-click').prop('disabled', false);
                    getRecaptcha('/', 'recaptcha-register');
                    getRecaptcha('/', 'recaptcha-restore-password');
                    getRecaptcha('/', 'recaptcha-form-goods-review');
                    getRecaptcha('/contacts', 'recaptcha-contacts');
                    getRecaptcha('/', 'recaptcha-subscribers');

                    /*$(".popup-error").find("ul").html('');
                    $(".popup-error").css('display', 'block');*/

                    $.each(response.messages, function (ObjNames, ObjValues) {
                        if (ObjNames == 'agree')
                            form.find("[name='" + ObjNames + "']").parent().find('.aggreement-checkbox').addClass('error-input');
                        else if (ObjNames === 'rating') {
                            form.find("[name='" + ObjNames + "']").closest('.product-reviews-appreciate-wrapper').append('<label class="error ' + ObjNames + '" for="' + ObjNames + '">' + '<strong>' + ObjValues + '</strong>' + '</label>');
                        } else if (ObjNames === 'promocod')
                            form.find("[name='" + ObjNames + "']").after('<label class="error-promocode ' + ObjNames + '" for="' + ObjNames + '">' + '<strong>' + ObjValues + '</strong>' + '</label>');
                        else if (ObjNames === 'subscribers_email')
                            form.find("[name='" + ObjNames + "']").after('<label class="error-subscribe ' + ObjNames + '" for="' + ObjNames + '">' + '<strong>' + ObjValues + '</strong>' + '</label>');
                        else {
                            form.find("[name='" + ObjNames + "']").addClass('error-input');
                            form.find("[name='" + ObjNames + "']").after('<label class="error ' + ObjNames + '" for="' + ObjNames + '">' + '<strong>' + ObjValues + '</strong>' + '</label>');
                            /*if (form_id == 'update-profile' || form_id == 'new-address') {
                                form.find("[name='" + ObjNames + "']").after('<label class="error-inline ' + ObjNames + '" for="' + ObjNames + '">' + '<strong>' + ObjValues + '</strong>' + '</label>');
                            } else {
                                form.find("[name='" + ObjNames + "']").after('<label class="error ' + ObjNames + '" for="' + ObjNames + '">' + '<strong>' + ObjValues + '</strong>' + '</label>');
                            }*/
                        }

                        //$(".popup-error").find("ul").append('<li>' + ObjValues + '</li>');
                    });

                    setTimeout(function () {
                        $(".error-input").removeClass('error-input');
                        //Remove error message
                        form.find('label.error').remove();
                        form.find('label.error-promocode').remove();
                        form.find('label.error-subscribe').remove();
                        form.find('label.error-inline').remove();
                        //$(".popup-error").fadeOut();
                    }, timeout_error);
                } else {

                    getRecaptcha('/', 'recaptcha-register');
                    getRecaptcha('/', 'recaptcha-restore-password');
                    getRecaptcha('/', 'recaptcha-form-goods-review');
                    getRecaptcha('/contacts', 'recaptcha-contacts');
                    getRecaptcha('/', 'recaptcha-subscribers');

                    Notiflix.Notify.failure(response.message, {
                            position: 'center-top',
                            timeout: timeout_error
                        }
                    );
                }
            }
        },
        error: function () {
            $(prevent_repeated).prop('disabled', false);
            getRecaptcha('/', 'recaptcha-register');
            getRecaptcha('/', 'recaptcha-restore-password');
            getRecaptcha('/', 'recaptcha-form-goods-review');
            getRecaptcha('/contacts', 'recaptcha-contacts');
            getRecaptcha('/', 'recaptcha-subscribers');
            Notiflix.Loading.remove();
        },
        complete: function () {
            Notiflix.Loading.remove();
            $(prevent_repeated).prop('disabled', false);
        }
    })
}

function getDefaultPriceFormat(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    //return parseInt(price);
}

/*function addToCart(parentThat) {

    let form_id = $(parentThat).data('form-id');
    $('#' + form_id).submit(function (event) {
        event.preventDefault();
    });

    if (!$(form_id)) {
        return;
    }

    let form = $('#' + $(parentThat).data('form-id'));
    let serializedForm = $(form).find("select, textarea, input").serializeArray();
    $.ajax({
        method: "POST",
        url: $(form).attr('action'),
        data: serializedForm,
        success: function (response) {
            if (response.status == true) {
                $('.header-basket-count').html(response.basket_count);

                Notiflix.Notify.success(response.message, {
                        position: 'center-top',
                        timeout: 3000,
                    }
                );
            }
            else {
                Notiflix.Notify.failure(response.message, {
                        position: 'center-top',
                        timeout: 3000
                    }
                );
            }
        }
    })
}*/


//Add goods to cart
$(document).ready(function () {
    $(document).on('click', '.add-to-basket, .product-end-add-to-basket', function (e) {

        e.preventDefault();
        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ajaxAddToCart';
        let goods_item_id = $(this).data('goods-item-id');
        let show_notiflix = $(this).data('show-notiflix');
        let page = $(this).data('page');
        //count value (+-) from main-page
        let goods_count = $('#goods-id-' + goods_item_id).val();
        let _this = $(this);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: url,
            data: {
                goods_item_id: goods_item_id,
                number: goods_count,
                page: page
            },
            success: function (response) {
                if (response.status == true) {
                    $('.header-basket-count').show();
                    $('.header-basket-count').html(response.basket_count);
                    $('.header-basket-price').html(getDefaultPriceFormat(response.total_price));
                    $('.render-header-basket-items').html(response.header_basket_items_view);

                    $('.render-modal-add-to-basket').html(response.modal_add_to_basket);
                    $('.render-right-header-basket').html(response.modal_show_basket);

                    //For GA4 and FB Pixel
                    if (response.goods_object) {
                        onProductClick('add_to_cart', response.goods_object)
                        onProductClickFB('AddToCart', response.goods_object)

                        //For FB Pixels

                    }

                    if (page == 'cabinet-wish' || show_notiflix === 1) {
                        Notiflix.Notify.success(response.message, {
                                position: 'center-top',
                                timeout: 3000
                            }
                        );
                    }
                }
            }
        })
    });
});

//Delete Item from basket
$(document).on('click', '.remove-basket-item', function (e) {
    e.preventDefault();

    let _this = $(this);
    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxDestroyItemCart';
    let goods_item_id = $(this).data('goods-item-id');
    //let change_delivery_method = $(".change-delivery-method:checked").val();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    //console.log(_this.parents('.basket-item').siblings('.free-gift').fadeOut());
    $.ajax({
        type: "POST",
        url: url,
        data: {
            goods_item_id: goods_item_id,
            //change_delivery_method: change_delivery_method
        },
        success: function (response) {
            if (response.status == true) {

                //Header
                $('.header-basket-count').html(response.basket_count);
                //$('.header-basket-price').html(getDefaultPriceFormat(response.sub_total_price));
                //$('.render-header-basket-items').html(response.header_basket_items_view);

                //For GA4
                if (response.goods_object) {
                    onProductClick('remove_from_cart', response.goods_object)
                }

                Notiflix.Notify.success(response.message, {
                        position: 'center-top',
                        timeout: 1000
                    }
                );

                if (response.basket_count > 0) {
                    //Basket

                    _this.parents('.basket-item').next('.free-gift').remove();
                    _this.parents('.basket-item').remove();


                    $('.basket-subtotal-price').html(getDefaultPriceFormat(response.sub_total));
                    $('.basket-total-price').html(getDefaultPriceFormat(response.total_price));

                    $('.header-basket-count').html(response.basket_count);
                    $('.basket-count').html(response.basket_count);

                    $('.basket-delivery-price').html(getDefaultPriceFormat(response.costul_livrarei));
                    $('.basket-for-free-delivery').html(getDefaultPriceFormat(response.pina_livrare));


                    //Remove from header-basket
                    _this.parents('.basket-modal-item').fadeOut();

                    if (response.costul_livrarei > 0)
                        $('.for-free-delivery-row').show();
                    else
                        $('.for-free-delivery-row').hide();

                } else {
                    $('.header-basket-count').hide();
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            }
        }
    });
});

// Remove all items from cart
$(document).on('click', '.remove-all-items', function (e) {
    e.preventDefault();

    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxDestroyAllItemsCart';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        type: "POST",
        url: url,
        beforeSend: function () {
            Notiflix.Loading.standard();
        },
        success: function (response) {
            if (response.status == true) {
                setTimeout(function () {
                    location.reload();
                }, 1000);
            }
        },
        error: function () {
            Notiflix.Loading.remove();
        },
        complete: function () {
            Notiflix.Loading.remove();
        }
    });
});

//Update basket
$('.update-cart').on('click', function () {
    Notiflix.Loading.standard();
    setTimeout(function () {
        location.reload();
        Notiflix.Loading.remove();
    }, 1000);
});

// Change goods count(+,-) on cart page
$(document).on('click', '.count-minus-change, .count-plus-change', function (e) {
    e.preventDefault();
    let _this = $(this).parent().find('input');
    diffSumCart(_this);
});

$('.basket-quantity-change').on('change', function (e) {
    e.preventDefault();
    let _this = $(this);
    diffSumCart(_this);
});

//Count items(-+) in item page and cart
function diffSumCart(_this) {

    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxDiffSumItemCart';
    let goods_item_id = _this.data('goods-item-id');
    let page = _this.data('page');
    let promo_id = _this.data('promo');
    let cadou_id = _this.data('cadou');
    let number = _this.val();

    if (_this.val() <= 0)
        _this.val('1');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        type: "POST",
        url: url,
        data: {
            goods_item_id: goods_item_id,
            page: page,
            number: number,
            cadou_id: cadou_id
        },
        success: function (response) {
            if (response.status == true) {

                $('.header-basket-count').html(response.basket_count);
                $('.basket-subtotal-price').html(getDefaultPriceFormat(response.sub_total));
                switch (response.page) {
                    case 'cart':
                        //For cart page
                        _this.parents('.basket-item').find('.one-item-total-price').html(getDefaultPriceFormat(response.total_item_price));
                        $('.basket-total-price').html(getDefaultPriceFormat(response.total_price));
                        $('.basket-delivery-price').html(getDefaultPriceFormat(response.costul_livrarei));
                        $('.basket-for-free-delivery').html(getDefaultPriceFormat(response.pina_livrare));
                        $('.basket-count').html(response.basket_count);
                        $('.discount').html(parseInt(response.discount_goods_price));

                        $('#item-price-' + goods_item_id).html(getDefaultPriceFormat(response.item_price));

                        /*if (response.show_discount == 1)*/
                        $('#item-discount-' + goods_item_id + ' .item-real-price').html(getDefaultPriceFormat(response.item_real_price));
                        /*else
                            $('#item-discount-' + goods_item_id + ' .item-real-price').html('');*/

                        if (response.discount_text)
                            $('.discount-offer-' + promo_id).html(response.discount_text);

                        if (response.discount_goods_price > 0)
                            $('.show-discount').removeClass('d-none');
                        else
                            $('.show-discount').addClass('d-none');

                        if (response.costul_livrarei > 0)
                            $('.for-free-delivery-row').show();
                        else
                            $('.for-free-delivery-row').hide();

                        if (response.cadou_min > 0 && number >= response.cadou_min)
                            $('.cadou-' + goods_item_id).removeAttr('disabled');
                        else
                            $('.cadou-' + goods_item_id).attr('disabled', 'disabled').prop('checked', false);

                        break;
                    case 'header-cart':
                        //For header cart
                        _this.parents('.basket-modal-item-text').find('.one-item-total-price').html(getDefaultPriceFormat(response.total_item_price));

                        break;

                    default:
                        break;
                }
            }
        }
    })
}

function newOrder(parentThat) {

    let lang = $('html').attr('lang');
    let url = '/' + lang;

    let form_id = $(parentThat).data('form-id');
    $('#' + form_id).submit(function (event) {
        event.preventDefault();
    });

    let form = $('#' + form_id);

    //For delivery price
    let district_id = $('#checkout-district-id').find("option:selected").val();

    //For fast orders count
    let fast_order_item_count = $('.quantity-item-page input').val();

    let validate_class = 'error';
    if (form_id == 'new-fast-order')
        validate_class = 'error-no-absolute'

    let order_form = new FormData(form[0]);
    order_form.append('district_id', district_id);
    order_form.append('fast_order_item_count', fast_order_item_count);

    let timeout_success = 2000;
    let timeout_error = 6000;
    let show_notiflix = 0;

    switch (form_id) {
        case 'new-fast-order':
            timeout_success = 3600000;
            timeout_error = 6000;
            break;
        case 'new-order':
            timeout_success = 0;
            timeout_error = 6000;
            break;
        default:
            break;
    }

    $('.prevent-repeated-click').prop('disabled', true);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        method: "POST",
        url: $(form).attr('action'),
        beforeSend: function () {
            Notiflix.Loading.standard();
        },
        data: order_form,
        enctype: 'multipart/form-data',
        processData: false,  // Important!
        contentType: false,
        cache: false,
        success: function (response) {
            //Remove error message
            form.find('label.error').remove();
            form.find('label.error-basket').remove();
            //Recaptcha reset
            getRecaptcha('/cart', 'recaptcha-order-new');
            getRecaptcha('/cart', 'recaptcha-order-already');
            getRecaptcha('/cart', 'recaptcha-order-without');

            if (response.status == true) {

                //$('.one-click').removeClass('active');
                closeCommonModal('.common-modal');
                openCommonModal('.succes-fast-order');


                if (show_notiflix === 1) {
                    Notiflix.Notify.success(response.message, {
                            position: 'center-top',
                            timeout: timeout_success,
                        }
                    );
                }

                setTimeout(function () {
                    if (response.redirect != null) {
                        window.location.href = response.redirect;
                    }
                }, timeout_success);

            } else if (response.status === 'warning') {

                Notiflix.Notify.warning(response.message, {
                        position: 'center-top',
                        plainText: false,
                        messageMaxLength: 500,
                        timeout: 180000,
                    }
                );

                $('.prevent-repeated-click').prop('disabled', false);

            } else {

                $('.prevent-repeated-click').prop('disabled', false);

                //Recaptcha reset
                getRecaptcha('/cart', 'recaptcha-order-new');
                getRecaptcha('/cart', 'recaptcha-order-already');
                getRecaptcha('/cart', 'recaptcha-order-without');

                if (response.messages != null) {

                    $(".popup-error").find("ul").html('');
                    $(".popup-error").css('display', 'block');
                    $.each(response.messages, function (ObjNames, ObjValues) {

                        if (ObjNames == 'agree') {
                            form.find("[name='" + ObjNames + "']").parent().find('.aggreement-checkbox').addClass('error-input');
                        } /*else if (ObjNames === 'delivery_area' || ObjNames === 'delivery_method') {
                            form.find("[name='" + ObjNames + "']").closest('.basket-payment-list').append('<label class="error error-delivery ' + ObjNames + '" for="' + ObjNames + '">' + '<strong>' + ObjValues + '</strong>' + '</label>');
                        }*/ else {
                            form.find("[name='" + ObjNames + "']").addClass('error-input');
                            form.find("[name='" + ObjNames + "']").after('<label class="' + validate_class + ' ' + ObjNames + '" for="' + ObjNames + '">' + '<strong>' + ObjValues + '</strong>' + '</label>');
                        }

                        $(".popup-error").find("ul").append('<li>' + ObjValues + '</li>');
                    });

                    setTimeout(function () {
                        $(".error-input").removeClass('error-input');
                        //Remove error message
                        form.find('label.' + validate_class).fadeOut();
                        form.find('label.error-basket').fadeOut();
                        $(".popup-error").fadeOut();
                    }, timeout_error);

                } else {
                    Notiflix.Notify.failure(response.message, {
                            timeout: timeout_error
                        }
                    );
                }
            }
        },
        error: function () {
            $('.prevent-repeated-click').prop('disabled', false);
            getRecaptcha('/cart', 'recaptcha-order-new');
            getRecaptcha('/cart', 'recaptcha-order-already');
            getRecaptcha('/cart', 'recaptcha-order-without');
            Notiflix.Loading.remove();
        }, complete: function () {
            Notiflix.Loading.remove();
        }
    });
}

//Reset form fast order
$(document).on('click', '.open-one-click', function () {
    $('#new-fast-order')[0].reset();
    $('.prevent-repeated-click').prop('disabled', false);
});


///for filter
$('#filter-data input:not(.filters-search-input), #filter-data select').on('change', function () {
    let my_form_id = $(this).parents('#filter-data').get(0);
    filterForm(my_form_id);
});

///Filter
function filterForm(parentThat) {

    let form_id = $(parentThat).data('form-id');
    $('#' + form_id).submit(function (event) {
        event.preventDefault();
    });

    $('[data-type="ckeditor"]').each(function (index, el) {
        $(this).val(CKEDITOR.instances.body.getData())
    });

    let form = $('#' + $(parentThat).data('form-id'));
//	var search_form = $('#search-form').find('input[name=s]');
    let serializedForm = $(form).find("select, textarea, input").serializeArray();

    serializedForm.push({name: 'data-parent', value: form.attr('data-parent')});
//	serializedForm.push({name: 's', value: search_form.val()});

    if (!$(form)) {
        return;
    }
    $.ajax({
        method: "POST",
        url: $(form).attr('action'),
        beforeSend: function () {
            Notiflix.Loading.standard();
        },
        data: serializedForm,
        success: function (response) {
            if (response.status == true) {
                //$('span.total-count').html(response.total_elements);
                $('.goods-items-count').html(response.goods_items_count);

                let newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + response.messages;
                window.history.pushState({path: newUrl}, '', newUrl);

                $('.render-products-list').html(response.view);
                $('.filters-remove').addClass('d-block')

                let goods_parameter_values_ids = response.goods_parameter_values_ids;
                let goods_brand_ids = response.goods_brand_ids;
                let goods_subject_ids = response.goods_subject_ids;
                let goods_type_ids = response.goods_type_ids;

                $('.filters-list input:not(input[name=_token], #minPrice, #maxPrice, #price-promo, #new, #in-stoc)').each(function (key, value) {
                    if (!$(this).hasClass('no-disabled') && $(this).prop('checked') == false) {
                        $(this).prop('disabled', true);
                    }
                });

                if ($('.goods-parameter-values-ids').length > 0) {
                    $.each(goods_parameter_values_ids, function (index, value) {
                        $('input[value="' + value + '"]').prop('disabled', false);
                    });
                }

                if ($('.parent-brands').length > 0) {
                    $.each(goods_brand_ids, function (index, value) {
                        $('input[value="' + value + '"]').prop('disabled', false);
                    });
                }

                if ($('.goods-subject-ids').length > 0) {
                    $.each(goods_subject_ids, function (index, value) {
                        $('input[value="' + value + '"]').prop('disabled', false);
                    });
                }

                if ($('.goods-type-ids').length > 0) {
                    $.each(goods_type_ids, function (index, value) {
                        $('input[value="' + value + '"]').prop('disabled', false);
                    });
                }

                //For brands
                $('.parent-brands').each(function () {
                    $(this).find('.filters-sub-item-list li').each(function () {
                        if ($(this).find('input:not(:disabled)').length > 0) {
                            $(this).closest('.parent-brands').find('.filters-submenu-head input').prop('disabled', false);
                            return false;
                        }
                    });
                });

                setTimeout(function () {
                    //$('.sk-cube-grid').fadeOut();
                    /* $('.render-products-list').fadeIn(500);*/
                }, 1000);

//					if(response.total_elements == 0) {
//						$('.product').html('<div class="empty-list"><span> No items</span></div>');
//					}
            } else {
                let newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.pushState({path: newUrl}, '', newUrl);
                //location.reload();
            }
        },
        error: function () {
            //$('.sk-cube-grid').fadeOut(500);
            Notiflix.Loading.remove();
        },
        complete: function () {
            Notiflix.Loading.remove();
        }
    })
}

$(document).on('click', '.click-param', function () {
    let parameter_id = $(this).data('parameter-id');
    $('.filters-list input').removeClass('no-disabled');
    //if ($(this).prop('checked') == true) {
    $('.filters-list input').each(function (key, value) {
        if ($(this).data('parameter-id') === parameter_id) {
            $(this).addClass('no-disabled');
            $(this).prop('disabled', false);
        }
    });
})

//For filter brands
$('.parent-brands').each(function () {
    $(this).find('.filters-sub-item-list li').each(function () {
        if ($(this).find('input:not(:disabled)').length > 0) {
            $(this).closest('.parent-brands').find('.filters-submenu-head input').prop('disabled', false);
            return false;
        }
    });
});

$(document).on('click', '.add-to-wish, .product-end-add-to-wish', function () {

    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxAddToWish';
    let goods_item_id = $(this).data('goods-item-id');
    let _this = $(this);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        type: "POST",
        url: url,
        data: {
            'goods_item_id': goods_item_id,
        },
        success: function (response) {
            if (response.status == true) {

                $('.header-wish-count').show();
                $('.header-wish-count').html(response.wish_count);
                _this.toggleClass('active');

                //For GA4 and FB Pixel
                if (response.goods_object) {
                    onProductClick('add_to_wishlist', response.goods_object)
                    onProductClickFB('AddToWishlist', response.goods_object)
                }

                Notiflix.Notify.success(response.message, {
                        position: 'center-top',
                        timeout: 2000,
                    }
                );

            } else {
                Notiflix.Notify.failure(response.message, {
                        position: 'center-top',
                        timeout: 2000,
                    }
                );
            }
        }
    });
});

$(document).on('click', '.delete-wish-item', function () {

    let goods_item_id = $(this).data('goods-item-id');
    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxDestroyWish';
    let _this = $(this);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        type: "POST",
        url: url,
        data: {
            goods_item_id: goods_item_id,
        },
        success: function (response) {
            if (response.status == true) {
                if (response.wish_count > 0) {
                    _this.closest('.wish-row-item').remove();
                    $('.header-wish-count').html(response.wish_count);

                    Notiflix.Notify.success(response.message, {
                            position: 'center-top',
                            timeout: 2000
                        }
                    );

                    //Calculate total price
                    $('.wish-total-price').html(response.wish_total_price);
                    $('.wish-total-promo-price').html(response.wish_total_promo_price);


                } else {
                    $('.header-wish-count').hide();
                    Notiflix.Notify.success(response.message, {
                            position: 'center-top',
                            timeout: 2000
                        }
                    );

                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                }
            }
        }
    });
});

$(document).ready(function () {
    $(document).on('click', '.add-all-wish-to-basket', function (e) {

        e.preventDefault();
        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ajaxAddAllWishToBasket';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: url,
            beforeSend: function () {
                Notiflix.Loading.standard();
            },
            success: function (response) {
                if (response.status == true) {
                    if (response.redirect != null) {
                        window.location.href = response.redirect;
                    }
                }
            },
            complete: function () {
                Notiflix.Loading.remove();
            }
        })
    });
});

$('.change-sorting').on('change', function (e) {
    e.preventDefault();
    changeSort('sorting', $(this).val());
});

$('.change-goods-per-page').on('change', function (e) {
    e.preventDefault();
    changeSort('goods_per_page', $(this).val());
});

function changeSort(sort_type, sort_value) {

    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxSortPage';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: url,
        beforeSend: function () {
            Notiflix.Loading.standard();
        },
        data: {
            sort_type: sort_type,
            sort_value: sort_value
        },
        success: function (response) {
            //$('.preloader__wrapper').removeClass('active');

            if (response.status == true) {
                if (response.redirect != null) {
                    window.location.href = response.redirect;
                } else {
                    location.reload();
                }
            }
        },
        error: function () {
            location.reload();
        },
        complete: function () {
            Notiflix.Loading.remove();
        }
    })
}

$(document).ready(function () {
    $(document).on('click', '.goods-open-modal', function () {
        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ajaxOpenGoodsModal';
        let goods_item_id = $(this).data('goods-item-id');
        //let page = $(this).data('page');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: url,
            data: {
                goods_item_id: goods_item_id,
                //page: page,
            },
            success: function (response) {
                if (response.status == true) {
                    $('.render-goods-modal').html(response.goods_modal_view);
                }
            }
        })
    });
});

$(document).ready(function () {
    $(document).on('click', '.show-order-details', function () {
        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ajaxShowOrderDetails';
        let order_id = $(this).data('order-id');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: url,
            data: {
                order_id: order_id,
            },
            success: function (response) {
                if (response.status == true) {
                    $('.render-order-details').html(response.goods_order_details_view);
                }
            }
        })
    });
});

$(document).ready(function () {
    $(document).on('click', '.repeat-order', function () {
        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ajaxRepeatOrder';
        let order_id = $(this).data('order-id');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: url,
            beforeSend: function () {
                Notiflix.Loading.standard();
            },
            data: {
                order_id: order_id,
            },
            success: function (response) {
                if (response.status == true) {
                    if (response.redirect != null) {
                        window.location.href = response.redirect;
                    }
                }
            },
            complete: function () {
                Notiflix.Loading.remove();
            }
        })
    });
});

$(document).ready(function () {
    $(document).on('click', '.goods-quick-view-modal', function () {
        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ajaxQuickViewGoods';
        let goods_item_id = $(this).data('goods-item-id');
        //let page = $(this).data('page');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: url,
            data: {
                goods_item_id: goods_item_id,
                //page: page,
            },
            success: function (response) {
                if (response.status == true) {
                    $('.render-modal-quick-view').html(response.goods_modal_view);
                }
            }
        })
    });
});

/*$(document).on('change', '.select-user-address', function (e) {

    e.preventDefault();
    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxSelectUserAddress';

    let user_address_id = $(this).find("option:selected").val();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        url: url,
        type: "POST",
        data: {
            user_address_id: user_address_id
        }, success: function (response) {
            if (response.status == true) {
                $('input[name=default_user_address]').remove();
                $('.user-address').html(response.selected_user_address_view);
            }
        }
    });
});*/

/*$(document).on('change', '.select-default-address', function (e) {

    e.preventDefault();
    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxSelectDefaultAddress';

    let user_address_id = $(".select-default-address:checked").data('user-address-id');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        url: url,
        type: "POST",
        data: {
            user_address_id: user_address_id
        }, success: function (response) {
            if (response.status == true) {

                Notiflix.Notify.success(response.message, {
                        position: 'center-top',
                        timeout: 2000
                    }
                );
            }
        }
    });
});*/

//Delete Item from basket
/*$(document).on('click', '.remove-address', function (e) {
    e.preventDefault();

    let _this = $(this);
    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxDestroyAddress';
    let user_address_id = $(this).data('user-address-id');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        type: "POST",
        url: url,
        data: {
            user_address_id: user_address_id,
        },
        success: function (response) {
            if (response.status == true) {

                Notiflix.Notify.success(response.message, {
                        position: 'center-top',
                        timeout: 1000
                    }
                );

                if (response.user_addresses_count > 1) {
                    _this.parents('li').fadeOut();
                    $('#saved-address-' + response.update_default_address).prop('checked', true);
                } else {
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            }
        }
    });
});*/

/*$(document).on('change', '#cities-agency, #agencies-sort', function (e) {
    location.href = $(this).val();
});*/


function cookieSetting(arg = null) {
    $('#cookie-close').prop('checked', false);
    switch (arg) {
        case 'all':
            $('.cookie-input').prop('checked', true);
            saveForm($('.cookie-save'));
            break;
        case 'clear':
            $('.cookie-input').prop('checked', false);
            saveForm($('.cookie-save'));
            break;
        case 'close':
            $('#cookie-close').prop('checked', true);
            saveForm($('.cookie-save'));
            break;
        default:
            saveForm($('.cookie-save'));
    }
}

/*$(document).on('change', '.select-county', function (e) {

    e.preventDefault();
    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxSelectLocality';
    let select_id = $(this).attr('id');
    let county_name = $(this).find("option:selected").val();


    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        beforeSend: function () {
            Notiflix.Block.standard('.js-loader-counties[data-select-id="' + select_id + '"]');
        },
        url: url,
        type: "POST",
        data: {
            county_name: county_name
        },
        success: function (response) {
            if (response.status == true) {
                $('[data-select-id="' + select_id + '"] .select-street').html(response.localities_view);
            }
        },
        complete: function () {
            Notiflix.Block.remove('.js-loader-counties[data-select-id="' + select_id + '"]');
        }
    });
});*/

/*$(document).on('change', '.select-street', function (e) {

    e.preventDefault();
    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxSelectStreet';
    let select_id = $(this).attr('id');
    let locality_name = $(this).find("option:selected").val();
    let county_name = $(this).find("option:selected").data('county-name');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        beforeSend: function () {
            Notiflix.Block.standard('.js-loader-streets[data-select-id="' + select_id + '"]');
        },
        url: url,
        type: "POST",
        data: {
            locality_name: locality_name,
            county_name: county_name,
        },
        success: function (response) {
            if (response.status == true) {
                $('[data-select-id="' + select_id + '"] .render-streets').html(response.streets_view);
            }
        },
        complete: function () {
            Notiflix.Block.remove('.js-loader-streets[data-select-id="' + select_id + '"]');
        }
    });
});*/

/*$(document).on('click', '.show-edit-address', function (e) {

    e.preventDefault();
    let lang = $('html').attr('lang');
    let url = '/' + lang + '/ajaxShowEditAddress';

    let button_edit_address_id = $(this).attr('id');
    let user_address_id = $(this).data('user-address-id');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.ajax({
        beforeSend: function () {
            Notiflix.Block.standard('.js-loader-edit-address-' + user_address_id, {
                    svgSize: '25px',
                    svgColor: '#26649B',
                }
            );
        },
        url: url,
        type: "POST",
        data: {
            user_address_id: user_address_id,
        },
        success: function (response) {
            if (response.status == true) {
                $('.render-edited-address[data-edit-button-id="' + button_edit_address_id + '"]').html(response.user_edit_address_view);
            }
        },
        complete: function () {
            Notiflix.Block.remove('.js-loader-edit-address-' + user_address_id);
        }
    });
});*/

//Update selected goods promo
$(document).ready(function () {
    $('.select-gift').on('click', function () {

        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ajaxSelectPromoGift';
        let goods_id_related = $(this).data('related-id');
        let goods_promo_id = $(this).data('promo-id');
        let basket_id = $(this).data('basket-id');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: url,
            data: {
                goods_id_related: goods_id_related,
                goods_promo_id: goods_promo_id,
                basket_id: basket_id
            },
            success: function (response) {
                if (response.status == true) {
                    Notiflix.Notify.success(response.message, {
                            position: 'center-top',
                            timeout: 2000
                        }
                    );
                } else {
                    Notiflix.Notify.failure(response.message, {
                            position: 'center-top',
                            timeout: 4000
                        }
                    );
                }
            }
        })
    });
});

$(document).ready(function () {
    $('.select-district').on('change', function (e) {
        e.preventDefault();
        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ajaxSelectDistrict';
        let district_id = $(this).find("option:selected").val();
        let total_price = $(this).data('total-price');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: url,
            data: {
                district_id: district_id,
                total_price: total_price,
            },
            success: function (response) {
                if (response.status == true) {
                    $('.basket-delivery-price').html(getDefaultPriceFormat(response.costul_livrarei));
                    $('.basket-total-price').html(getDefaultPriceFormat(response.total_price));

                    if (response.show_currency_message === 1)
                        $('.show-other-currency').removeClass('d-none').fadeIn();
                    else
                        $('.show-other-currency').addClass('d-none').fadeOut();
                }
            }
        })
    });
});


$(document).ready(function () {
    $('.change-delivery-method').on('change', function (e) {

        e.preventDefault();

        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ajaxChangeDeliveryMethod';
        let total_price = $(this).data('total-price');
        let delivery_method = $('option:selected', this).val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: url,
            data: {
                delivery_method: delivery_method,
                total_price: total_price
            },
            success: function (response) {
                if (response.status == true) {

                    switch (delivery_method) {
                        case 'pickup':
                            $(".pickup").removeClass('d-none').fadeIn();
                            $(".nova-courier").addClass('d-none');
                            $(".nova-terminal").addClass('d-none');
                            $(".delivery-address").addClass('d-none');
                            break;
                        case 'nova_courier':
                            $(".nova-courier").removeClass('d-none').fadeIn();
                            $(".pickup").addClass('d-none').fadeOut();
                            $(".nova-terminal").addClass('d-none').fadeOut();
                            $(".delivery-address").addClass('d-none');
                            break;
                        case 'nova_terminal':
                            $(".nova-terminal").removeClass('d-none').fadeIn();
                            $(".pickup").addClass('d-none').fadeOut();
                            $(".nova-courier").addClass('d-none').fadeOut();
                            $(".delivery-address").addClass('d-none');
                            break;
                        default:
                            $(".delivery-address").removeClass('d-none').fadeIn();
                            $(".pickup").addClass('d-none').fadeOut();
                            $(".nova-courier").addClass('d-none').fadeOut();
                            $(".nova-terminal").addClass('d-none');
                            break;
                    }

                    //reset
                    //$('.select-district').prop('selectedIndex', 0);
                    $('.basket-delivery-price').html(getDefaultPriceFormat(response.costul_livrarei));
                    $('.basket-total-price').html(getDefaultPriceFormat(response.total_price));
                }
            }
        })
    });
});


$(document).on('mouseenter', '.show-submenu-img-l1', function (e) {
    e.preventDefault();
    let img_source = $(this).data('img-source');
    $(this).closest('.has-submenu').find('.submenu-img img').attr("src", img_source);
});

//For filter brand
function checkAllBox(data) {
    if ($(data).prop('checked')) {
        $(data).parent().addClass('closed').siblings('.filters-sub-item-list').stop().show().find('input').prop('checked', true)
    } else {
        $(data).parent().siblings('.filters-sub-item-list').find('input').prop('checked', false)
    }
}

$(document).on('click', '.check-sub-brand', function () {
    if ($(this).closest('.filters-sub-item-list').find('input:checked').length == $(this).closest('.filters-sub-item-list').find('input').length) {
        $(this).closest('.custom-check-row').find('.filters-submenu-head input').prop("checked", true);
    } else {
        $(this).closest('.custom-check-row').find('.filters-submenu-head input').prop("checked", false);
    }
});

$(document).ready(function () {
    $('.custom-check-row').each(function () {
        if ($(this).find('.filters-sub-item-list').find('input:checked').length != 0) {
            $(this).find('.filters-submenu-head').addClass('closed').siblings('.filters-sub-item-list').show();
        } else {
            $(this).find('.filters-submenu-head').removeClass('closed').siblings('.filters-sub-item-list').hide();
        }
    })
});

$('.ajax-search-form input').on('click', function () {

    $('.main-header-search').addClass('active');
    $('.header-search-bg').show();
    $('.search-results').show();

    let search_value = $(this).val();
    if (search_value.length > 0) {
        $('.render-all-search').show();
    } else {
        $('.render-all-search').hide();
        $('.default-search-popular').removeClass('d-none');
        $('.default-search-products').removeClass('d-none');
    }
});

let search_time = null;
$('.ajax-search-form input').on('keyup', function () {
    clearTimeout(search_time);
    let search_value = $(this).val();
    search_time = setTimeout(function () {
        $('.search_form_results').hide();
        if (search_value.length > 0) {
            let lang = $('html').attr('lang');
            let url = '/' + lang + '/ajaxGoodSearch';
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    'search_value': search_value
                },
                success: function (response) {
                    if (response.status == true) {
                        if (response.search_items_view) {
                            $('.default-search-popular').addClass('d-none');
                            $('.default-search-products').addClass('d-none');
                            $('.render-all-search').html(response.search_items_view).show();
                        }
                    }
                }
            });
        } else {
            $('.default-search-popular').removeClass('d-none');
            $('.default-search-products').removeClass('d-none');
            $('.render-all-search').hide();
        }
    }, 300)
});

//For GA4
function onProductClick(event, productObj) {
    dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
    dataLayer.push({
        event: event,
        ecommerce: {
            items: [{
                item_name: productObj.item_name,
                item_id: productObj.item_id,
                item_category: productObj.item_category,
                item_brand: productObj.item_brand,
                quantity: productObj.quantity,
                price: productObj.price
            }]
        }
    });
}

//For GA4
function onCheckout(productObjects, total_price) {
    dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
    dataLayer.push({
        event: "begin_checkout",
        ecommerce: {
            currency: "MDL",
            value: total_price,
            items: productObjects
        }
    });
}


//For GA4
$(document).ready(function () {
    $(document).on('click', '.ga4-promo-click', function () {
        let lang = $('html').attr('lang');
        let url = '/' + lang + '/ga4PromoClick';
        let promo_id = $(this).data('promo-id');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: url,
            data: {
                promo_id: promo_id,
            },
            success: function (response) {
                if (response.status == true) {

                    if (response.promo_goods_objects) {
                        dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
                        dataLayer.push({
                            event: "select_promotion",
                            ecommerce: {
                                items: response.promo_goods_objects
                            }
                        });
                    }
                }
            }
        })
    });
});

//For FB Pixel
function onProductClickFB(event, productObject) {
    fbq('track', event, {
            content_name: productObject.item_name,
            content_ids: productObject.item_id,
            content_category: productObject.item_category,
            content_type: 'product',
            currency: 'MDL',
            value: productObject.price
        }
    );
}

//For FB Pixel
function onCheckoutFB(productsIds, total_price, total_count) {
    fbq('track', 'InitiateCheckout', {
        content_type: 'product',
        content_ids: productsIds,
        num_items: total_count,
        value: total_price,
        currency: 'MDL'
    });
}
