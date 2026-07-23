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
                        <div class="position-relative">
                            <form method="GET" action="{{urlForFunctionLanguage($lang, 'search/searchObjects')}}">
                                <div class="input-group">
                                    <input type="text" name="search-key" class="form-control"
                                           placeholder="{{ __('variables.search_object_it') }}"
                                           value="{{ $concrete_search_key ?? '' }}">
                                    <button type="submit" class="input-group-text btn-success btn-search"><i
                                            class="bx bx-search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="ms-auto">
                            @if($groupSubRelations->new == 1)
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                                {{--<a href="{{ urlForFunctionLanguage($lang, 'createLabel/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>--}}
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @endif
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroySubscriber/destroySubscriber')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($subscribers) && count($subscribers))
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">{{__('variables.id_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.email')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody data-url="{{ $url_for_active_elem }}">
                                @foreach($subscribers as $one_subscribe)
                                    <tr class="row-id" data-id="{{$one_subscribe->id}}">
                                        <th class="text-center"
                                            scope="row">{{ $subscribers->firstItem() + $loop->index }}</th>
                                        <td class="text-center">
                                            <span>{{ $one_subscribe->email ?? '' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_subscribe->created_at) }}</span>
                                        </td>
                                        @if($groupSubRelations->del_to_rec == 1 || $groupSubRelations->del_from_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$one_subscribe->id}}"
                                                       id="destroy-element-{{$one_subscribe->id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$one_subscribe->id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $subscribers])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
