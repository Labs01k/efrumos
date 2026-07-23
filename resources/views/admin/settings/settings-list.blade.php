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
                                <a href="{{ urlForFunctionLanguage($lang, 'createSetting/createitem') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="bx bxs-plus-square"></i>{{ __('variables.add_element') }}
                                </a>
                            @else
                                <a href="{{ urlForFunctionLanguage($lang, '') }}"
                                   class="btn btn-primary mt-2 mt-lg-0"><i
                                        class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            @endif
                            @if($groupSubRelations->del_to_rec == 1)
                                <button class="btn btn-danger btn-sm mt-2 mt-lg-0 destroy-all-elements"
                                        data-url="{{urlForFunctionLanguage($lang, 'destroySettingFromCart/destroySettingFromCart')}}"
                                        data-current-url="{{ url()->current() }}" disabled>
                                    <i class="fas fa-trash"></i> {{ __('variables.delete_selected') }} (<span>0</span>)
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr/>
                    @if(!empty($settings_list_id) && count($settings_list_id))
                        <div class="table-responsive table-responsive-scrollbar-top"></div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.title_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.alias_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.edit_table')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.position_table')}}</th>
                                    @if($groupSubRelations->del_to_rec == 1)
                                        <th scope="col"
                                            class="text-center select-all-elements cursor-pointer">{{__('variables.delete_table')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody class="sort-table" data-url="{{ $url_for_active_elem }}">
                                @foreach($settings_list_id as $key => $setting)
                                    <tr class="row-id" data-id="{{$setting->id}}">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <span>{{ $setting->itemByLang->name ?? str_replace('-', ' ', Str::ucfirst($setting->alias)) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$setting->alias}}</span><br>
                                            @if(Auth::user()->root == 1)
                                                <span
                                                    class="text-secondary">showSettingBodyByAlias('{{ $setting->alias ?? '' }}')</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($setting->lang_dependent == 1)
                                                @foreach($lang_list as $lang_key => $one_lang)
                                                    <a href="{{urlForFunctionLanguage($lang, $setting->id .'/edititem/'.$setting->id.'/'.$lang_key)}}"
                                                       class="btn btn-sm btn-{{ !empty(IfHasName($setting->id, $lang_key, 'settings')) ? 'success' : 'danger' }}">{{Str::ucfirst($one_lang)}}</a>
                                                @endforeach
                                            @else
                                                <a href="{{urlForFunctionLanguage($lang, $setting->id .'/edititem/'.$setting->id.'/'.$lang_id)}}"
                                                   class="btn btn-sm btn-success">{{ __('variables.edit') }}</a>
                                            @endif
                                        </td>
                                        <td class="position cursor-pointer text-center"><i class="lni lni-move"></i>
                                        @if($groupSubRelations->del_to_rec == 1)
                                            <td class="text-center">
                                                <input class="form-check-input destroy-element" type="checkbox"
                                                       name="destroy_element"
                                                       value="{{$setting->id}}"
                                                       id="destroy-element-{{$setting->id}}">
                                                <label class="form-check-label"
                                                       for="destroy-element-{{$setting->id}}"></label>
                                            </td>

                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @include('admin.templates.pagination', ['paginator' => $settings_list_id])
                    @else
                        @include('admin.templates.empty-list')
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

