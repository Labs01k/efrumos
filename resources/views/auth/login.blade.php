@extends('admin.app')

@section('content')


    <div class="wrapper">
        <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
            <div class="container-fluid">
                <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                    <div class="col mx-auto">
                        <div class="mb-4 text-center">
                            <img src="{{ asset('admin-assets/images/logo.png') }}" alt="Webit">
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="border p-4 rounded">
                                    <div class="text-center">
                                        <h3>{{ __('variables.sing_in') }}</h3>
                                    </div>
                                    <div class="form-body">
                                        <form method="POST" action="{{ route('login.ajaxLogin') }}{{--{{ url($lang.'/back/auth/login') }}--}}" class="row g-3" id="login-form">
                                            @csrf
                                            <div class="col-12">
                                                <label for="login" class="form-label">{{ __('variables.sing_in') }}</label>
                                                <input type="text" name="login" class="form-control" id="login"
                                                       placeholder="{{ __('variables.sing_in') }}">
                                            </div>
                                            <div class="col-12">
                                                <label for="password" class="form-label">{{ __('variables.password_text') }}</label>
                                                <div class="input-group" id="show_hide_password">
                                                    <input type="password" name="password" class="form-control border-end-0"
                                                           id="password" placeholder="{{ __('variables.password_text') }}"> <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" name="remember" type="checkbox"
                                                           id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="remember">{{ __('variables.remember_me') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary" onclick="saveForm(this)" data-form-id="login-form"><i class="bx bxs-lock-open"></i>{{__('variables.sing_in')}}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

