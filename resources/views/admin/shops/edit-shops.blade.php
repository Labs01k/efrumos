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
                            <h5 class="card-title">{{__('variables.edit_element')}} - <span
                                    class="text-primary">"{{$shops->name ?? '' }}"</span></h5>
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
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($shops_without_lang->name).'/edititem/'.$shops_without_lang->shops_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($shops_without_lang->name).'/edititem/'.$shops_without_lang->shops_id.'/'.$lang_id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'save/'.$shops_without_lang->shops_id.'/'.$lang_to_edit) }}"
                          id="edit-form" data-parent-url="{{$url_for_active_elem}}" enctype="multipart/form-data">

                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-8 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{$shops->name ?? ''}}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="alias"
                                                   class="form-label">{{__('variables.alias_table')}}</label>
                                            <input type="text" name="alias" class="form-control" id="alias"
                                                   value="{{ $shops_without_lang->shopsId->alias ?? ''}}">
                                        </div>

                                        <div class="mb-3">
                                            <label for="address"
                                                   class="form-label">{{__('variables.address')}}</label>
                                            <input type="text" name="address" class="form-control"
                                                   id="address" value="{{$shops->address ?? ''}}">
                                        </div>

                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="phone"
                                                       class="form-label">{{__('variables.phone')}}</label>
                                                <input type="text" name="phone" class="form-control"
                                                       id="phone"
                                                       value="{{ $shops_without_lang->shopsId->phone ?? ''}}">
                                            </div>

                                            <div class="mb-3mb-3 col-md-6">
                                                <label for="schedule"
                                                       class="form-label">{{__('variables.schedule')}}</label>
                                                <input type="text" name="schedule" class="form-control"
                                                       id="schedule" value="{{$shops->schedule ?? ''}}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="store_guid"
                                                       class="form-label">{{ __('variables.shop_store_guid') }}</label>
                                                <input type="text" name="store_guid" class="form-control"
                                                       id="store_guid"
                                                       value="{{ $shops_without_lang->shopsId->store_guid ?? '' }}">
                                            </div>

                                            {{-- Поле «Карта (iframe)» убрано: страница магазинов
                                                 показывает одну общую интерактивную карту, отдельные
                                                 врезки больше не выводятся. Данные в базе сохранены —
                                                 из них взяты координаты магазинов. --}}

                                            <div class="mb-3 col-md-12">
                                                <label for="google_place_id"
                                                       class="form-label">{{__('variables.google_place_id')}}</label>
                                                <input type="text" name="google_place_id" class="form-control"
                                                       id="google_place_id" value="{{ $shops_without_lang->shopsId->google_place_id ?? ''}}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="upload_files"
                                                       class="form-label">{{__('variables.select_file')}}</label>
                                                <input class="form-control" type="file" name="upload_files[]"
                                                       id="upload_files" multiple="">
                                            </div>

                                            @include('admin.templates.uploaded-images', ['upload_path' => 'shops'])

                                            {{--<div class="mb-3 col-md-6">
                                                <label for="latitude"
                                                       class="form-label">{{__('variables.latitude')}}</label>
                                                <input type="text" name="latitude" class="form-control"
                                                       id="latitude"
                                                       value="{{$shops_without_lang->shopsId->latitude ?? ''}}">
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label for="longitude"
                                                       class="form-label">{{__('variables.longitude')}}</label>
                                                <input type="text" name="longitude" class="form-control"
                                                       id="longitude"
                                                       value="{{$shops_without_lang->shopsId->longitude ?? ''}}">
                                            </div>--}}
                                        </div>

                                       {{-- <div class="d-grid">
                                            <button type="button" class="btn btn-secondary btn-block"
                                                    id="show-google-map">{{__('variables.map')}}</button>
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
                                                            <option
                                                                value="{{$lang_key}}" {{$lang_key == $lang_to_edit ? 'selected' : ''}}>{{Str::ucfirst($one_lang)}}</option>
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
                                                            <option
                                                                value="{{$one_city->city_id}}" {{$shops_without_lang->shopsId->city_id == $one_city->city_id ? 'selected' : ''}}>{{$one_city->name ?? ''}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            @if($groupSubRelations->save == 1)
                                                <div class="col-12">
                                                    <div class="d-grid">
                                                        <button class="btn btn-success"
                                                                onclick="saveForm(this)"
                                                                data-form-id="edit-form">{{__('variables.save_it')}}
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
