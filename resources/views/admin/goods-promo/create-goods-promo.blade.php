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
                                <a href="{{ urlForFunctionLanguage($lang, 'createGoodsPromo/creategoodspromo') }}"
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

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'save') }}"
                          id="edit-form" enctype="multipart/form-data">
                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <div class="border border-3 p-4 rounded">

                                        <div class="mb-3">
                                            <label for="promo_type"
                                                   class="form-label">{{__('variables.promo_type')}}</label>
                                            <select class="form-select" name="promo_type" id="promo_type">
                                                <option value="1">Discont (%)</option>
                                                <option value="2">1 + 1 = 3</option>
                                                <option value="3">1 + Cadou</option>
                                                <option value="4">Promocod</option>
                                                <option value="5">X Cant =% DISCOUNT</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="data_start"
                                                       class="form-label">{{__('variables.start_promo')}}</label>
                                                <input type="text" name="data_start" class="form-control date-flatpickr"
                                                       id="data_start">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="data_end"
                                                       class="form-label">{{__('variables.end_promo')}}</label>
                                                <input type="text" name="data_end" class="form-control date-flatpickr"
                                                       id="data_end">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="discount_procent"
                                                       class="form-label">{{__('variables.percent_sale')}}</label>
                                                <input type="text" name="discount_procent" class="form-control"
                                                       id="discount_procent">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="discount_summa"
                                                       class="form-label">{{__('variables.sale_sum')}}</label>
                                                <input type="text" name="discount_summa" class="form-control"
                                                       id="discount_summa">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="cant_pentru_disc"
                                                       class="form-label">{{__('variables.product_amount')}}</label>
                                                <input type="text" name="cant_pentru_disc" class="form-control"
                                                       id="cant_pentru_disc">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="cant_cadou"
                                                       class="form-label">{{__('variables.present_amount')}}</label>
                                                <input type="text" name="cant_cadou" class="form-control"
                                                       id="cant_cadou">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="promocod"
                                                   class="form-label">{{__('variables.promo_code')}}</label>
                                            <input type="text" name="promocod" class="form-control" id="promocod">
                                        </div>

                                        <div class="position-relative d-flex justify-content-between mt-4">
                                            <div>
                                                <h6 class="card-title">{{__('variables.tag_settings')}}</h6>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mt-1 mb-3">
                                            <input type="checkbox" class="form-check-input" name="show_tag_in_products"
                                                   id="show_tag_in_products">
                                            <label class="form-check-label"
                                                   for="show_tag_in_products">{{__('variables.show_text_in_products')}}</label>
                                        </div>

                                        @if(!empty($lang_list) && count($lang_list))
                                            @foreach($lang_list as $lang_id => $one_lang)
                                                <div class="mb-3">
                                                    <label for="tag_name_{{ $one_lang ?? '' }}"
                                                           class="form-label">{{__('variables.text_promo_tag')}}
                                                        ({{ $one_lang ?? '' }})</label>
                                                    <input type="text" name="tag_name[{{ $lang_id ?? '' }}]"
                                                           class="form-control" id="tag_name_{{ $one_lang ?? '' }}">
                                                </div>
                                            @endforeach
                                        @endif

                                        <div class="mb-3">
                                            <label for="tag_color"
                                                   class="form-label">{{__('variables.promo_color')}} (ex. #15ca20)</label>
                                            <input type="text" name="tag_color" class="form-control" id="tag_color">
                                        </div>

                                        @if($groupSubRelations->save == 1)
                                            <div class="d-grid">
                                                <button class="btn btn-success"
                                                        onclick="saveForm(this)"
                                                        data-form-id="edit-form">{{__('variables.save_it')}}
                                                </button>
                                            </div>
                                        @endif
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

@push('other-scripts')
    <script>
        $('.date-flatpickr').flatpickr({
            altFormat: "j F Y",
            enableTime: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i",
            disableMobile: "true",
            "locale": "{{ $lang }}"
        });
    </script>
@endpush
