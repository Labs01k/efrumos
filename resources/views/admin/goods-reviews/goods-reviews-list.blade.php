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
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
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
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyReviewItem/destroyReviewItem')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($goods_with_reviews) && count($goods_with_reviews))
                        <div class="table-responsive table-responsive-scrollbar-top"></div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    @if(!$goods_item_id)
                                        <th scope="col" class="text-center">{{__('variables.product')}}</th>
                                    @endif
                                    <th scope="col" class="text-center">{{__('variables.item_review')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.rating')}}</th>
                                    <th scope="col" class="text-center">{{ __('variables.user_name') }}</th>
                                    <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.active_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($goods_with_reviews as $key => $one_goods_review)
                                    <tr class="row-id" data-id="{{$one_goods_review->id}}">
                                        <th class="text-center" scope="row">{{ $goods_with_reviews->firstItem() + $loop->index }}</th>
                                        @if(!$goods_item_id)
                                            <td class="text-center">
                                                <a style="text-decoration: underline;"
                                                   href="{{ url($lang, ['back', 'goods-reviews'])}}?item={{ $one_goods_review->goods_item_id }}">{{ IfHasName($one_goods_review->goods_item_id, LANG_ID, 'goods_item') }}</a>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            <button class="btn btn-sm" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapseReviewDetails{{ $one_goods_review->id ?? '' }}"
                                                    aria-expanded="false"
                                                    aria-controls="collapseExample"><i class="lni lni-eye text-primary"></i></button>
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $one_goods_review->rating ?? '' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($one_goods_review->frontUserId)
                                                <a style="text-decoration: underline;"
                                                   href="{{ url($lang, ['back', 'front-user', $one_goods_review->front_user_id, 'editUser', $one_goods_review->front_user_id, LANG_ID])}}">{{ $one_goods_review->frontUserId->last_name . ' ' . $one_goods_review->frontUserId->name ?? '' }}</a>
                                            @else
                                                <span>{{trans('variables.do_not_exist')}}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_goods_review->created_at) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$one_goods_review->active}}"
                                                       data-element-id="{{$one_goods_review->id}}"
                                                       data-action="main-active"
                                                       id="switch-active-{{$one_goods_review->id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$one_goods_review->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$one_goods_review->id}}"></label>
                                            </div>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$one_goods_review->id}}"
                                                       id="destroy-element-{{$one_goods_review->id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$one_goods_review->id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                    <tr id="collapseReviewDetails{{ $one_goods_review->id ?? '' }}" class="collapse">
                                        <td colspan="12">
                                            <div>
                                                <div class="card card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap text-wrap">
                                                            <span>{{ $one_goods_review->review_text ?? '' }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $goods_with_reviews])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
