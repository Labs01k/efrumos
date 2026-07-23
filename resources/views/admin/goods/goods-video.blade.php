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
                            <h5 class="card-title">{{__('variables.add_video')}} - <span
                                    class="text-primary">"{{$goods_item_id->itemByLang->name ?? '' }}"</span></h5>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @else
                                <a href="{{ urlForLanguage($lang, 'memberslist') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @endif
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyGoodsItemVideo/destroyGoodsItemVideo')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }}
                                    (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>

                    <hr/>

                    <form class="form" method="POST" action="{{ urlForLanguage($lang, 'saveGoodsItemVideo') }}"
                          id="add-video-form" style="margin-bottom: 1rem;"
                          enctype="multipart/form-data">

                        @csrf
                        <input type="hidden" name="goods_item_id" value="{{ $goods_item_id->id ?? '' }}">

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <div class="border border-3 p-4 rounded">
                                        <div class="mb-3">
                                            <label for="youtube_link"
                                                   class="form-label">{{__('variables.youtube_id')}}</label>
                                            <input type="text" name="youtube_link" class="form-control"
                                                   id="youtube_link" data-url="{{ $url_for_active_elem }}" value="">
                                        </div>

                                        <div class="mb-3 col-md-6">
                                            <div class="youtube_id"></div>
                                        </div>

                                        @if($groupSubRelations->save == 1)
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button class="btn btn-success"
                                                            onclick="saveForm(this)"
                                                            data-form-id="add-video-form">{{__('variables.save_it')}}
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if($goods_item_id && $goods_item_id->goodsVideosForBack->isNotEmpty())
                        <div class="card border border-3 mt-4 rounded shadow-none">
                            <div class="card-body">
                                <div class="d-lg-flex align-items-center mb-4 gap-3">
                                </div>
                                <div class="table-responsive">
                                    <table class="table mb-0 table-hover">
                                        <thead>
                                        <tr>
                                            <th scope="col" class="text-center">№</th>
                                            <th scope="col" class="text-center">{{__('variables.video')}}</th>
                                            @if($groupSubRelations->active == 1)
                                                <th scope="col"
                                                    class="text-center">{{__('variables.active_table')}}</th>
                                            @endif
                                            <th scope="col"
                                                class="text-center">{{__('variables.position_table')}}</th>
                                            <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                            @if($groupSubRelations->del_to_rec == 1)
                                                <th scope="col"
                                                    class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody class="sort-table" data-url="{{ $url_for_active_elem }}"
                                               data-action="item-video">
                                        @foreach($goods_item_id->goodsVideosForBack as $one_video)
                                            <tr class="row-id"
                                                data-id="{{$one_video->id ?? ''}}">
                                                <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                                <td class="text-center">
                                                    <a href="https://www.youtube.com/embed/{{$one_video->youtube_id ?? '' }}?autoplay=0"
                                                       data-fancybox="gallery">
                                                        <img src="https://img.youtube.com/vi/{{$one_video->youtube_id ?? '' }}/0.jpg"
                                                             width="70" height="70">
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-switch">
                                                        <input class="form-check-input change-active"
                                                               type="checkbox"
                                                               data-active="{{$one_video->active ?? ''}}"
                                                               data-element-id="{{$one_video->id ?? ''}}"
                                                               data-action="item-video"
                                                               id="switch-active-{{$one_video->id ?? ''}}"
                                                               data-url="{{$url_for_active_elem}}" {{$one_video->active == 1 ? 'checked' : ''}}>
                                                        <label class="form-check-label"
                                                               for="switch-active-{{$one_video->id ?? ''}}"></label>
                                                    </div>
                                                </td>
                                                <td class="position cursor-pointer text-center"><i
                                                        class="lni lni-move"></i>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_video->created_at) }}</span>
                                                </td>
                                                @if($groupSubRelations->del_to_rec == 1)
                                                    <td class="text-center">
                                                        <input class="form-check-input destroy-element"
                                                               type="checkbox"
                                                               name="destroy_element"
                                                               value="{{$one_video->id ?? ''}}"
                                                               id="destroy-element-{{$one_video->id ?? ''}}">
                                                        <label class="form-check-label"
                                                               for="destroy-element-{{$one_video->id ?? ''}}"></label>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

