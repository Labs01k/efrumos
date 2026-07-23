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
                                    class="text-primary">"{{$goods_promo->name ?? ''}}"</span></h5>
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
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($goods_promo->name).'/editGoodsPromo/'.$goods_promo->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                <a href="{{ urlForFunctionLanguage($lang, Str::slug($goods_promo->name).'/editGoodsPromo/'.$goods_promo->id) }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="fadeIn animated bx bx-edit"></i>{{ __('variables.edit_element') }}
                                </a>
                            @endif
                                @if($groupSubRelations->del_to_rec == 1)
                                    <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                            data-url="{{urlForFunctionLanguage($lang, 'destroyGoodsPromoItems/destroyGoodsPromoItems')}}"
                                            data-current-url="{{ url()->current() }}" disabled>
                                        <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }}
                                        (<span>0</span>)
                                    </button>
                                @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'save/'.$goods_promo->id) }}"
                          id="edit-form" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="one_c_id" value="{{ $goods_promo->id or '' }}">

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <div class="border border-3 p-4 rounded">

                                        <div class="mb-3">
                                            <label for="promo_type"
                                                   class="form-label">{{__('variables.promo_type')}}</label>
                                            <select class="form-select" name="promo_type" id="promo_type">
                                                <option
                                                    value="1" {{ $goods_promo->promo_type == 1 ? 'selected' : ''}}>
                                                    Discont (%)
                                                </option>
                                                <option
                                                    value="2" {{ $goods_promo->promo_type == 2 ? 'selected' : ''}}>1
                                                    + 1 = 3
                                                </option>
                                                <option
                                                    value="3" {{ $goods_promo->promo_type == 3 ? 'selected' : ''}}>1
                                                    + Cadou
                                                </option>
                                                <option
                                                    value="4" {{ $goods_promo->promo_type == 4 ? 'selected' : ''}}>
                                                    Promocod
                                                </option>
                                                <option
                                                    value="5" {{ $goods_promo->promo_type == 5 ? 'selected' : ''}}>X
                                                    Cant =%
                                                    DISCOUNT
                                                </option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="name" class="form-label">{{__('variables.title_table')}}</label>
                                            <input type="text" name="name" class="form-control" id="name"
                                                   value="{{ $goods_promo->name ?? '' }}">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="data_start"
                                                       class="form-label">{{__('variables.start_promo')}}</label>
                                                <input type="text" name="data_start" class="form-control date-flatpickr"
                                                       id="data_start"
                                                       value="{{ \Carbon\Carbon::parse($goods_promo->data_start)->format('Y-m-d H:i') }}">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="data_end"
                                                       class="form-label">{{__('variables.end_promo')}}</label>
                                                <input type="text" name="data_end" class="form-control date-flatpickr"
                                                       id="data_end"
                                                       value="{{ \Carbon\Carbon::parse($goods_promo->data_end)->format('Y-m-d H:i') }}">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="discount_procent"
                                                       class="form-label">{{__('variables.percent_sale')}}</label>
                                                <input type="text" name="discount_procent" class="form-control"
                                                       id="discount_procent"
                                                       value="{{ $goods_promo->discount_procent ?? '' }}">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="discount_summa"
                                                       class="form-label">{{__('variables.sale_sum')}}</label>
                                                <input type="text" name="discount_summa" class="form-control"
                                                       id="discount_summa"
                                                       value="{{ $goods_promo->discount_summa ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="cant_pentru_disc"
                                                       class="form-label">{{__('variables.product_amount')}}</label>
                                                <input type="text" name="cant_pentru_disc" class="form-control"
                                                       id="cant_pentru_disc"
                                                       value="{{ $goods_promo->cant_pentru_disc ?? '' }}">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="cant_cadou"
                                                       class="form-label">{{__('variables.present_amount')}}</label>
                                                <input type="text" name="cant_cadou" class="form-control"
                                                       id="cant_cadou"
                                                       value="{{ $goods_promo->cant_cadou ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="promocod"
                                                   class="form-label">{{__('variables.promo_code')}}</label>
                                            <input type="text" name="promocod" class="form-control" id="promocod"
                                                   value="{{ $goods_promo->promocod ?? '' }}">
                                        </div>


                                        <div class="position-relative d-flex justify-content-between mt-4">
                                            <div>
                                                <h6 class="card-title">{{__('variables.tag_settings')}}</h6>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mt-1 mb-3">
                                            <input type="checkbox" class="form-check-input" name="show_tag_in_products"
                                                   id="show_tag_in_products" {{$goods_promo->show_tag_in_products == 1 ? 'checked' : ''}}>
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
                                                           class="form-control" id="tag_name_{{ $one_lang ?? '' }}"
                                                           value="{!! $goods_promo_lang[$lang_id]->tag_name ?? '' !!}">
                                                </div>
                                            @endforeach
                                        @endif

                                        <div class="mb-3">
                                            <label for="tag_color"
                                                   class="form-label">{{__('variables.promo_color')}} (ex. #15ca20)</label>
                                            <input type="text" name="tag_color" class="form-control" id="tag_color"
                                                   value="{{ $goods_promo->tag_color ?? '' }}">
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

                    <div class="position-relative d-flex justify-content-between mt-4">
                        <div>
                            <h5 class="card-title">{{__('variables.goods_for_promo')}} -
                                <strong>{{ $goods_promo->name ?? '' }}</strong>
                                / {{__('variables.promo_type')}} -
                                <strong>{{ getPromoType($goods_promo->promo_type) }}</strong></h5>
                        </div>
                    </div>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'savePromoItem') }}"
                          id="save-promo-form" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="goods_promo_id" value="{{ $goods_promo->id ?? '' }}">
                        <input type="hidden" name="goods_promo_type" value="{{ $goods_promo->promo_type ?? '' }}">

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <div class="border border-3 p-4 rounded">

                                        <div class="mb-3">
                                            <label for="one_c_code" class="form-label">{{__('variables.1c_code')}}
                                                / {{__('variables.articol')}}</label>
                                            <input type="text" name="one_c_code" class="form-control" id="one_c_code">
                                        </div>

                                        @if(!empty($goods_subject_list) && count($goods_subject_list))
                                            <div class="mb-3">
                                                <label for="goods_subject"
                                                       class="form-label">{{__('variables.subject_element')}}</label>
                                                <select name="goods_subject[]" id="goods_subject"
                                                        class="form-select multiple-select" multiple>
                                                    @foreach($goods_subject_list as $one_subject)
                                                        <option
                                                            value="{{ $one_subject->id ?? '' }}">{{ $one_subject->name ?? '' }}
                                                            - {{IfHasName($one_subject->p_id, $lang_id, 'goods_subject')}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        @if(!empty($brands_list) && count($brands_list))
                                            <div class="mb-3">
                                                <label for="brands_list"
                                                       class="form-label">{{__('variables.brand_element')}}</label>
                                                <select name="brands_list[]" id="brands_list"
                                                        class="form-select multiple-select" multiple>
                                                    @foreach($brands_list as $one_brand)
                                                        <option
                                                            value="{{ $one_brand->id ?? '' }}">{{ $one_brand->name ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="product_cadou"
                                                       id="is_produs" value="is_produs" checked>
                                                <label class="form-check-label" for="is_produs">
                                                    {{__('variables.product')}}
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="product_cadou"
                                                       id="is_cadou" value="is_cadou">
                                                <label class="form-check-label" for="is_cadou">
                                                    {{__('variables.cadou')}}
                                                </label>
                                            </div>
                                        </div>

                                        @if($groupSubRelations->save == 1)
                                            <div class="d-grid">
                                                <button class="btn btn-success"
                                                        onclick="saveForm(this)"
                                                        data-form-id="save-promo-form">{{__('variables.save_it')}}
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="position-relative d-flex justify-content-between mt-4 mb-4">
                        <div>
                            <h5 class="card-title">{{ __('variables.product') }}</h5>
                        </div>
                    </div>

                    @if(!empty($goods_promo_items) && count($goods_promo_items))

                        <div class="table-responsive">
                            <table class="table mb-0 table-bordered">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.1c_code')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.articol')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @if($goods_promo_items_produs && $goods_promo_items_produs > 0)
                                    <tr class="tr-products">
                                        <td colspan="6" class="text-center" style="background-color: #dee2e6;"><span><strong>{{__('variables.product')}}</strong></span></td>
                                    </tr>
                                @endif
                                @foreach($goods_promo_items as $key => $one_promo_item)
                                    @if($one_promo_item->is_produs == 1)
                                        <tr class="row-id" data-id="{{$one_promo_item->id}}">
                                            <td class="text-center">
                                                <span>{{ $one_promo_item->getGoodsItemId->itemByLang->name ?? '' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span>{{ $one_promo_item->getGoodsItemId->one_c_code ?? '' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span>{{ $one_promo_item->getGoodsItemId->articol ?? '' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_promo_item->created_at) }}</span>
                                            </td>
                                            @if($groupSubRelations->del_to_rec == 1)
                                                <td class="text-center">
                                                    <input class="form-check-input destroy-element" type="checkbox"
                                                           name="destroy_element"
                                                           value="{{$one_promo_item->id}}"
                                                           id="destroy-element-{{$one_promo_item->id}}">
                                                    <label class="form-check-label"
                                                           for="destroy-element-{{$one_promo_item->id}}"></label>
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                @endforeach
                                @if($goods_promo_items_codou && $goods_promo_items_codou > 0)
                                    <tr class="tr-products">
                                        <td colspan="6" class="text-center" style="background-color: #dee2e6;"><span><strong>{{__('variables.cadou')}}</strong></span></td>
                                    </tr>
                                @endif
                                @foreach($goods_promo_items as $key => $one_promo_item)
                                    @if($one_promo_item->is_cadou == 1)
                                        <tr class="row-id" data-id="{{$one_promo_item->id}}">
                                            <td class="text-center">
                                                <span>{{ $one_promo_item->getGoodsItemId->itemByLang->name ?? '' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span>{{ $one_promo_item->getGoodsItemId->one_c_code ?? '' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span>{{ $one_promo_item->getGoodsItemId->articol ?? '' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_promo_item->created_at) }}</span>
                                            </td>
                                            @if($groupSubRelations->del_to_rec == 1)
                                                <td class="text-center">
                                                    <input class="form-check-input destroy-element" type="checkbox"
                                                           name="destroy_element"
                                                           value="{{$one_promo_item->id}}"
                                                           id="destroy-element-{{$one_promo_item->id}}">
                                                    <label class="form-check-label"
                                                           for="destroy-element-{{$one_promo_item->id}}"></label>
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        @include('admin.templates.empty-list')
                    @endif
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
