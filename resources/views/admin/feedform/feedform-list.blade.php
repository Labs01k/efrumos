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
                            <a href="{{ urlForFunctionLanguage($lang, '') }}"
                               class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroyFeedformFromCart/destroyFeedformFromCart')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($feedform) && count($feedform))
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.name_text')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.email_text')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.phone')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.comment_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.edit_table')}}</th>
                                    {{--@if($groupSubRelations->active == 1)
                                        <th scope="col" class="text-center">{{__('variables.active_table')}}</th>
                                    @endif--}}
                                    @if($groupSubRelations->del_to_rec == 1 || $groupSubRelations->del_from_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody class="sort-table" data-url="{{ $url_for_active_elem }}">
                                @foreach($feedform as $one_feedform)
                                    <tr class="row-id" data-id="{{$one_feedform->id}}">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <span>{{$one_feedform->name . ' ' .$one_feedform->last_name ?? ''}}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="mailto:{{ $one_feedform->email ?? '' }}">{{ $one_feedform->email ?? '' }}</a>
                                        </td>
                                        <td class="text-center">
                                            <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', $one_feedform->phone) }}">{{$one_feedform->phone ?? ''}}</a>
                                        </td>
                                        <td class="text-center">
                                            <span>{{!empty($one_feedform->comment) ? strPosText($one_feedform->comment, 80) : ''}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_feedform->created_at) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{urlForFunctionLanguage($lang, Str::slug($one_feedform->name).'/edititem/'.$one_feedform->id)}}"
                                               class="btn btn-sm btn-success">{{__('variables.edit_table')}}</a>
                                        </td>
                                        {{--<td class="text-center">
                                            <div class="form-switch">
                                                <input class="form-check-input change-active" type="checkbox"
                                                       data-active="{{$one_feedform->active}}"
                                                       data-element-id="{{$one_feedform->id}}"
                                                       data-action="main-active"
                                                       id="switch-active-{{$one_feedform->id}}"
                                                       data-url="{{$url_for_active_elem}}" {{$one_feedform->active == 1 ? 'checked' : ''}}>
                                                <label class="form-check-label"
                                                       for="switch-active-{{$one_feedform->id}}"></label>
                                            </div>
                                        </td>--}}
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$one_feedform->id}}"
                                                       id="destroy-element-{{$one_feedform->id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$one_feedform->id}}"></label>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $feedform])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
