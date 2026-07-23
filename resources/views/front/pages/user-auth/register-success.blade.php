@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('checkout-success-register-page') }}
            </div>
        </div>

        <div class="basket-end">
            <div class="container">
                <div class="basket-end-inner">
                    <div class="basket-end-icon">
                        <img src="{{ asset('front-assets/img/icons/basket-success.svg') }}" alt="Success">
                    </div>
                    @if($email_message && $email_message->itemByLang)
                        <div class="basket-end-title">{{ $email_message->itemByLang->h1_title ?? '' }}</div>
                        <div class="basket-end-text">
                            <p>{{ $email_message->itemByLang->short_descr ?? '' }}</p>
                        </div>
                        <div class="basket-end-link">
                            <a href="javascript:;" class="open-login-modal">{{ $email_message->itemByLang->page_title ?? '' }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

@stop
