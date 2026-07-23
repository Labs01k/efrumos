@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('cabinet-password') }}
            </div>
        </div>

        <div class="cabinet">
            <div class="container">
                <div class="section-head">
                    <h1 class="h2">{{ ShowLabelById(63) }}</h1>
                </div>
                <div class="cabinet-inner">
                    @include('front.pages.cabinet.templates.menu')
                    <div class="cabinet-content">
                        <div class="cabinet-greeting"><b>{{ ShowLabelById(98) }}</b></div>
                        <div class="cabinet-form cabinet-limited-width">
                            <form method="POST"
                                  action="{{ route('ajax-update-password') }}" id="update-password"
                                  enctype="multipart/form-data">

                                @csrf

                                <div class="form-item">
                                    <label for="cabinet-current-password">{{ ShowLabelById(99) }}*</label>
                                    <input type="password" id="cabinet-current-password" name="current_password">
                                </div>

                                <div class="form-row form-row-2">

                                    <div class="form-item">
                                        <label for="cabinet-password">{{ ShowLabelById(100) }}*</label>
                                        <input type="password" id="cabinet-password" name="password">
                                    </div>
                                    <div class="form-item">
                                        <label for="cabinet-password-confirmation">{{ ShowLabelById(101) }}*</label>
                                        <input type="password" id="cabinet-password-confirmation"
                                               name="password_confirmation">
                                    </div>
                                </div>

                                <div class="cabinet-form-info">
                                    <p>{{ ShowLabelById(90) }}</p>
                                </div>
                                <div class="form-submit">
                                    <button type="submit" class="button button-black--inversed" onclick="saveForm(this)"
                                            data-form-id="update-password">{{ ShowLabelById(95) }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop
