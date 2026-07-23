@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('register-page') }}
            </div>
        </div>

        <div class="registration">
            <div class="container">
                <div class="registration-inner">
                    <h1 class="h2">{{ ShowLabelById(29) }}</h1>
                    <div class="basket-account-socials">
                        <p>{{ ShowLabelById(30) }}</p>
                        <ul>
                            <li>
                                <a href="{{ route('login-facebook') }}" class="social-facebook">{{ ShowLabelById(31) }}</a>
                            </li>
                            <li>
                                <a href="{{ route('login-google') }}" class="social-google">{{ ShowLabelById(32) }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="registration-text">
                        <p>{{ ShowLabelById(33) }}</p>
                    </div>
                    <div class="registration-form">
                        <form method="POST" action="{{ route('ajax-register-user') }}" id="register-user">

                            @csrf

                            <div class="form-item">
                                <label for="email">{{ ShowLabelById(34) }}*</label>
                                <input type="email" id="email" name="email">
                            </div>
                            <div class="form-row">
                                <div class="form-item">
                                    <label for="last_name">{{ ShowLabelById(35) }}*</label>
                                    <input type="text" id="last_name" name="last_name">
                                </div>
                                <div class="form-item">
                                    <label for="name">{{ ShowLabelById(36) }}*</label>
                                    <input type="text" id="name" name="name">
                                </div>
                            </div>
                            <div class="form-row form-row-3">
                                <div class="form-label">{{ ShowLabelById(37) }}</div>
                                @include('front.templates.birth-form-inputs')
                            </div>
                            <div class="form-item">
                                <label for="phone">{{ ShowLabelById(41) }}*</label>
                                <input type="number" id="phone" name="phone">
                            </div>
                            <div class="form-item">
                                <label for="password">{{ ShowLabelById(42) }}*</label>
                                <input type="password" id="password" name="password">
                            </div>
                            <div class="form-item">
                                <label for="password_confirmation">{{ ShowLabelById(43) }}*</label>
                                <input type="password" id="password_confirmation" name="password_confirmation">
                            </div>
                            <div class="google-policies">
                                <p>{!! ShowLabelById(27) !!}</p>
                            </div>
                            <p class="aggreement mt-1">
                                <label>
                                    <input id="register-agree" name="agree" type="checkbox">
                                    <span class="aggreement-checkbox"></span>
                                    {!! ShowLabelById(28) !!}
                                </label>
                            </p>

                            <div class="captcha">
                                <input type="hidden" name="g-recaptcha-response" id="recaptcha-register">
                            </div>

                            <div class="form-submit">
                                <button type="submit" class="button button--black prevent-repeated-click-register" onclick="saveForm(this)" data-form-id="register-user">
                                    {{ ShowLabelById(44) }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop
