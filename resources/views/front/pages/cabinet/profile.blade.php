@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('cabinet-page') }}
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
                        <div
                            class="cabinet-greeting">{!! str_replace('{user}', $global_user->last_name .' '.$global_user->name, ShowLabelById(89)) !!}</div>
                        <div class="cabinet-form cabinet-limited-width">
                            <form method="POST"
                                  action="{{ route('ajax-update-profile') }}" id="update-profile"
                                  enctype="multipart/form-data">

                                @csrf

                                <div class="cabinet-salutation">
                                    <p>{{ ShowLabelById(91) }}:</p>
                                    <ul>
                                        <li>
                                            <input type="radio" id="male" name="gender"
                                                   value="male" {{ $global_user->gender == 'male' ? 'checked' : ($global_user->gender == null ? 'checked' : '') }}>
                                            <label for="male">{{ ShowLabelById(92) }}</label>
                                        </li>
                                        <li>
                                            <input type="radio" id="female" name="gender"
                                                   value="female" {{ $global_user->gender == 'female' ? 'checked': '' }}>
                                            <label for="female">{{ ShowLabelById(93) }}</label>
                                        </li>
                                    </ul>
                                </div>
                                <div class="form-item">
                                    <label for="cabinet-email">{{ ShowLabelById(34) }}*</label>
                                    <input type="text" id="cabinet-email" name="email"
                                           value="{{ $global_user->email ?? '' }}" disabled>
                                </div>
                                <div class="form-item">
                                    <label for="cabinet-last-name">{{ ShowLabelById(35) }}*</label>
                                    <input type="text" id="cabinet-last-name" name="last_name"
                                           value="{{ $global_user->last_name ?? '' }}">
                                </div>
                                <div class="form-item">
                                    <label for="cabinet-name">{{ ShowLabelById(36) }}*</label>
                                    <input type="text" id="cabinet-name" name="name"
                                           value="{{ $global_user->name ?? '' }}">
                                </div>
                                <div class="form-item">
                                    <label for="cabinet-phone">{{ ShowLabelById(41) }}*</label>
                                    <input type="number" id="cabinet-phone" name="phone"
                                           value="{{ $global_user->phone ?? '' }}">
                                </div>

                                <div class="form-row form-row-2">
                                    <div class="form-item basket-account-address--district">
                                        <label for="district-id" class="">{{ ShowLabelById(214) }}*</label>
                                        <select name="district_id" id="district-id">
                                            <option value="">{{ ShowLabelById(45) }}</option>
                                            @if(!empty($districts) && count($districts))
                                                @foreach($districts as $one_district)
                                                    <option
                                                        value="{{ $one_district->id ?? '' }}" {{ $global_user && $global_user->district_id == $one_district->id ? 'selected' : '' }}>{{ $one_district->name ?? '' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="form-item basket-account-address--city">
                                        <label for="city">{{ ShowLabelById(215) }}*</label>
                                        <input type="text" id="city" name="city" placeholder="Scrie aici localitatea"
                                               value="{{ $global_user ? $global_user->city : '' }}">
                                    </div>
                                </div>

                                <div class="form-item basket-account-address--address">
                                    <label for="address">{{ ShowLabelById(146) }}*</label>
                                    <input type="text" id="address" name="address"
                                           placeholder="Strada, bloc, număr, apartament, alte indicații"
                                           value="{{ $global_user ? $global_user->address : '' }}">
                                </div>

                                <div class="form-row form-row-3">
                                    <div class="form-label">{{ ShowLabelById(94) }}</div>
                                    @include('front.templates.birth-form-inputs')
                                </div>

                                <div class="cabinet-form-info">
                                    <p>{{ ShowLabelById(90) }}</p>
                                </div>
                                <div class="form-submit">
                                    <button type="submit" class="button button-black--inversed" onclick="saveForm(this)"
                                            data-form-id="update-profile">{{ ShowLabelById(95) }}</button>
                                </div>
                            </form>
                        </div>
                        {{--<div class="cabinet-block">
                            <div class="cabinet-block-title">Adrese de livrare</div>
                            <div class="cabinet-block-list">
                                <div class="cabinet-block-item">
                                    <div class="cabinet-block-info">
                                        <p>Lungu Mariana</p>
                                        <p>+373 78 670934 </p>
                                        <p>Chișinău, Chișinău</p>
                                        <p>Str. Trandafirilor, 15, ap. 34</p>
                                    </div>
                                    <div class="cabinet-block-footer">
                                        <a href="#" class="cabinet-block-edit">
                                            <svg>
                                                <use xlink:href="svg/sprite.svg#edit"></use>
                                            </svg>
                                            <span>Modifică</span>
                                        </a>
                                        <a href="#">
                                            <svg>
                                                <use xlink:href="svg/sprite.svg#delete-2"></use>
                                            </svg>
                                            <span>Șterge</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="cabinet-block-item">
                                    <div class="cabinet-block-info">
                                        <p>Lungu Mariana</p>
                                        <p>+373 78 670934 </p>
                                        <p>Chișinău, Chișinău</p>
                                        <p>Str. Trandafirilor, 15, ap. 34</p>
                                    </div>
                                    <div class="cabinet-block-footer">
                                        <a href="#" class="cabinet-block-edit">
                                            <svg>
                                                <use xlink:href="svg/sprite.svg#edit"></use>
                                            </svg>
                                            <span>Modifică</span>
                                        </a>
                                        <a href="#">
                                            <svg>
                                                <use xlink:href="svg/sprite.svg#delete-2"></use>
                                            </svg>
                                            <span>Șterge</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="cabinet-block-btn">
                                <a href="javascript:;" class="button button-black--inversed open-new-address">
                                    <svg>
                                        <use xlink:href="svg/sprite.svg#plus"></use>
                                    </svg>
                                    <span>Adaugă Adresa</span>
                                </a>
                            </div>
                        </div>--}}
                        {{--<div class="cabinet-block">
                            <div class="cabinet-block-title">Adrese de facturare</div>
                            <div class="cabinet-block-btn">
                                <a href="javascript:;" class="button button-black--inversed open-new-address">
                                    <svg>
                                        <use xlink:href="svg/sprite.svg#plus"></use>
                                    </svg>
                                    <span>Adaugă Adresa</span>
                                </a>
                            </div>
                        </div>--}}
                        {{--<div class="cabinet-block">
                            <div class="cabinet-block-title">Date companie</div>
                            <div class="cabinet-block-text">
                                <p>Reprezinți o companie sau o societate comercială?</p>
                                <p>Înregistrează un cont pentru persoane juridice și beneficiazăde oferte și sistem de bonus.</p>
                            </div>
                            <div class="cabinet-block-btn">
                                <a href="javascript:;" class="button button-black--inversed open-new-address">
                                    <svg>
                                        <use xlink:href="svg/sprite.svg#plus"></use>
                                    </svg>
                                    <span>Adaugă companie</span>
                                </a>
                            </div>
                        </div>--}}
                        {{--<div class="cabinet-block">
                            <div class="cabinet-block-title">Puncte</div>
                            <div class="cabinet-block-text">
                                <p>Nu ai puncte disponibile.</p>
                            </div>
                        </div>--}}
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop
