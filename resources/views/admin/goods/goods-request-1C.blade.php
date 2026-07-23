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
                            <h5 class="card-title">{{ __('variables.request_1c_goods') }}</h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST"
                          action="{{ urlForLanguage($lang, 'actionRequestGoodsFrom1C') }}"
                          id="request-1c-form" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="current_url" value="{{ url()->current() }}">

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <div class="border border-3 p-4 rounded">

                                        <div class="mb-3">
                                            <label for="one_c_code" class="form-label">{{__('variables.1c_code')}} (ex. 105116 sau 105116,105500)</label>
                                            <input type="text" name="one_c_code" class="form-control" id="one_c_code">
                                        </div>

                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input goods-action" type="radio" name="goods_action"
                                                       id="show_goods" value="show_goods" checked>
                                                <label class="form-check-label" for="show_goods">
                                                    {{__('variables.show_goods_request')}}
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input goods-action" type="radio" name="goods_action"
                                                       id="update_goods" value="update_goods">
                                                <label class="form-check-label" for="update_goods">
                                                    {{__('variables.update_goods_request')}}
                                                </label>
                                            </div>
                                        </div>

                                        @if($groupSubRelations->save == 1)
                                            <div class="d-grid">
                                                <button class="btn btn-success"
                                                        onclick="saveForm(this)"
                                                        data-form-id="request-1c-form">{{__('variables.save_it')}}
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>


                    <div class="form-body mt-4 goods-request d-none">
                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                <div class="border border-3 p-4 rounded render-goods-request"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop
