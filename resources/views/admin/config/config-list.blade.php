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
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($config_list) && count($config_list))
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.config_current_value')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.config_new_value')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.save_it')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($config_list as $config_key => $config_value)
                                    <tr class="row-id">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <span>{{ $config_key ?? '' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{ $config_value ?? '' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <input type="text" name="config_item_value" class="form-control" id="{{ $config_key ?? '' }}">
                                        </td>
                                        <td class="text-center">

                                           <button class="btn btn-sm btn-success save-config-value"> <i class="fs-6 lni lni-save m-0"></i>
                                            </button>
                                        </td>
                                    </tr>
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

