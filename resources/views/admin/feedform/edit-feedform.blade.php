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
                            <a href="{{ urlForFunctionLanguage($lang, Str::slug($feedform->name).'/edititem/'.$feedform->id) }}"
                               class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                            </a>
                        </div>
                    </div>
                    <hr/>
                    <div class="table-responsive">
                        <table class="table mb-0 table-hover">
                            <thead>
                            <tr>
                                <th scope="col" class="text-center">{{__('variables.name_text')}}</th>
                                <th scope="col" class="text-center">{{__('variables.email_text')}}</th>
                                <th scope="col" class="text-center">{{__('variables.phone')}}</th>
                                <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                <th scope="col" class="text-center">{{__('variables.user_ip')}}</th>
                                {{--@if($groupSubRelations->active == 1)
                                    <th scope="col" class="text-center">{{__('variables.active_table')}}</th>
                                @endif--}}
                            </tr>
                            </thead>
                            <tbody class="sort-table" data-url="{{ $url_for_active_elem }}">
                            <tr class="row-id" data-id="{{$feedform->id}}">
                                <td class="text-center">
                                    <span>{{$feedform->name . ' ' .$feedform->last_name ?? ''}}</span>
                                </td>
                                <td class="text-center">
                                    <a href="mailto:{{ $feedform->email ?? '' }}">{{ $feedform->email ?? '' }}</a>
                                </td>
                                <td class="text-center">
                                    <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', $feedform->phone) }}">{{$feedform->phone ?? ''}}</a>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ getDefaultDateFormatAdmin($feedform->created_at) }}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{ $feedform->ip }}</span>
                                </td>
                                {{--<td class="text-center">
                                    <div class="form-switch">
                                        <input class="form-check-input change-active" type="checkbox"
                                               data-active="{{$feedform->active}}"
                                               data-element-id="{{$feedform->id}}"
                                               data-action="main-active"
                                               id="switch-active-{{$feedform->id}}"
                                               data-url="{{$url_for_active_elem}}" {{$feedform->active == 1 ? 'checked' : ''}}>
                                        <label class="form-check-label"
                                               for="switch-active-{{$feedform->id}}"></label>
                                    </div>
                                </td>--}}
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <div class="position-relative">
                        <h6 class="mb-0 text-uppercase">{{__('variables.comment_table')}} </h6>
                    </div>
                    <form class="form" method="POST" action="{{ urlForLanguage($lang, 'save/'.$feedform->id) }}"
                          id="edit-form"
                          enctype="multipart/form-data">

                        @csrf

                        <div class="form-body mt-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <textarea name="comment" class="form-control" id="body"
                                                  rows="5">{!! $feedform->comment ?? ''  !!}</textarea>
                                    </div>
                                    @if($groupSubRelations->save == 1)
                                        <div class="mb-3">
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
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
