@extends('admin.app')

@section('content')

    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    @include('admin.templates.breadcrumbs')
                </div>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="position-relative">
                            <h5 class="card-title">{{__('variables.add_element')}}</h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, 'createShops/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                            class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST" action="{{ urlForLanguage($lang, 'save') }}" id="add-form"
                          enctype="multipart/form-data">

                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name" autofocus>
                                        </div>

                                        <div class="mb-3">
                                            <label for="alias" class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias">
                                        </div>

                                        <div class="mb-3">
                                            <label for="address" class="form-label">{{__('variables.address')}}</label>
                                            <input type="text" name="address" class="form-control" id="address">
                                        </div>

                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="phone"
                                                       class="form-label">{{__('variables.phone')}}</label>
                                                <input type="text" name="phone" class="form-control"
                                                       id="phone">
                                            </div>

                                            <div class="mb-3mb-3 col-md-6">
                                                <label for="schedule"
                                                       class="form-label">{{__('variables.schedule')}}</label>
                                                <input type="text" name="schedule" class="form-control"
                                                       id="schedule">
                                            </div>

                                            <div class="mb-3">
                                                <label for="map_iframe"
                                                       class="form-label">{{__('variables.map')}} (iframe)</label>
                                                <input type="text" name="map_iframe" class="form-control"
                                                       id="map_iframe">
                                            </div>

                                            <div class="mb-3">
                                                <label for="store_guid"
                                                       class="form-label">{{ __('variables.shop_store_guid') }}</label>
                                                <input type="text" name="store_guid" class="form-control"
                                                       id="store_guid" value="">
                                            </div>

                                            <div class="mb-3">
                                                <label for="upload_files" class="form-label">{{__('variables.select_file')}}</label>
                                                <input class="form-control" type="file" name="upload_files[]"
                                                       id="upload_files" multiple="">
                                            </div>

                                            @include('admin.templates.upload-new-images')

                                           {{-- <div class="mb-3 col-md-6">
                                                <label for="latitude"
                                                       class="form-label">{{__('variables.latitude')}}</label>
                                                <input type="text" name="latitude" class="form-control"
                                                       id="latitude" value="{{ config('custom.back.default_latitude') }}">
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="longitude"
                                                       class="form-label">{{__('variables.longitude')}}</label>
                                                <input type="text" name="longitude" class="form-control"
                                                       id="longitude" value="{{ config('custom.back.default_longitude') }}">
                                            </div>--}}
                                        </div>

                                        {{--<div class="d-grid">
                                            <button type="button" class="btn btn-secondary btn-block" id="show-google-map">{{__('variables.map')}}</button>
                                        </div>

                                        <div id="google_map"></div>--}}

                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="row g-3">
                                            @if(!empty($lang_list) && count($lang_list))
                                                <div class="col-12">
                                                    <label for="lang"
                                                           class="form-label">{{__('variables.lang')}}</label>
                                                    <select class="form-select" name="lang" id="lang">
                                                        @foreach($lang_list as $lang_key => $one_lang)
                                                            <option value="{{$lang_key}}" {{$lang_key == $lang_id ? 'selected' : ''}}>{{Str::ucfirst($one_lang)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            @if(!empty($city) && count($city))
                                                <div class="col-12">
                                                    <label for="city_id"
                                                           class="form-label">{{__('variables.p_id_name')}}</label>
                                                    <select class="form-select" name="city_id" id="city_id">
                                                        @foreach($city as $one_city)
                                                            <option value="{{$one_city->city_id}}">{{$one_city->name ?? ''}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            @if($groupSubRelations->save == 1)
                                                <div class="col-12">
                                                    <div class="d-grid">
                                                        <button class="btn btn-success"
                                                                onclick="saveForm(this)"
                                                                data-form-id="add-form">{{__('variables.save_it')}}
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
