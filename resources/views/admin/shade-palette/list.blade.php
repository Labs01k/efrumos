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
                        <h5 class="mb-0">{{ __('variables.shades_title') }}</h5>
                        <div class="ms-auto">
                            <a href="{{ url(LANG . '/back/goods/shades/massupload') }}"
                               class="btn btn-primary mt-2 mt-lg-0">
                                <i class="bx bx-upload"></i> {{ __('variables.shades_mass_upload') }}
                            </a>
                        </div>
                    </div>

                    @if(session('shade_saved'))
                        <div class="alert alert-success">{{ __('variables.shades_photo_saved') }}</div>
                    @endif
                    @if(session('shade_error'))
                        <div class="alert alert-danger">{{ session('shade_error') }}</div>
                    @endif

                    <form method="GET" action="{{ url(LANG . '/back/goods/shades') }}"
                          class="row g-2 align-items-center mb-4">
                        <div class="col-md-4">
                            <input type="text" name="q" class="form-control" value="{{ $q }}"
                                   placeholder="{{ __('variables.shades_search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="brand" class="form-select">
                                <option value="">{{ __('variables.shades_all_lines') }}</option>
                                @foreach($brand_list as $one_brand)
                                    <option value="{{ $one_brand->id }}" {{ $brand_filter == $one_brand->id ? 'selected' : '' }}>
                                        {{ $one_brand->itemByLang->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-check ms-2">
                            <input type="checkbox" name="without_photo" value="1" class="form-check-input"
                                   id="without-photo" {{ $only_without ? 'checked' : '' }}>
                            <label class="form-check-label" for="without-photo">{{ __('variables.shades_without_photo') }}</label>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary">{{ __('variables.shades_find') }}</button>
                        </div>
                    </form>

                    @if($shades_list->count())
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover align-middle">
                                <thead>
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{ __('variables.shades_photo') }}</th>
                                    <th scope="col" class="text-center">{{ __('variables.shades_code') }}</th>
                                    <th scope="col">{{ __('variables.title_table') }}</th>
                                    <th scope="col" class="text-center">{{ __('variables.shades_articol') }}</th>
                                    <th scope="col" class="text-center">{{ __('variables.shades_line') }}</th>
                                    <th scope="col" class="text-center">{{ __('variables.shades_photo_actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($shades_list as $one_item)
                                    <tr @if(session('shade_saved') == $one_item->id) class="table-success" @endif>
                                        <th class="text-center" scope="row">{{ $one_item->id }}</th>
                                        <td class="text-center">
                                            @if($one_item->shade_img && file_exists(public_path('upfiles/goods-shades/s/' . showImg($one_item->shade_img))))
                                                <img src="{{ asset('upfiles/goods-shades/s/' . showImg($one_item->shade_img)) }}"
                                                     alt="" width="40" height="40"
                                                     style="object-fit: cover; border-radius: 50%;">
                                            @elseif($one_item->shade_img)
                                                <img src="{{ asset('upfiles/goods-shades/' . $one_item->shade_img) }}"
                                                     alt="" width="40" height="40"
                                                     style="object-fit: cover; border-radius: 50%;">
                                            @else
                                                <span class="text-muted">{{ __('variables.shades_no_photo') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($one_item->shade_code)
                                                <b>{{ $one_item->shade_code }}</b>
                                            @else
                                                <span class="text-danger">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $one_item->itemByLang->name ?? '' }}</td>
                                        <td class="text-center">{{ $one_item->articol ?: '—' }}</td>
                                        <td class="text-center">{{ $one_item->getBrand->itemByLang->name ?? '—' }}</td>
                                        <td class="text-center">
                                            <form method="POST" enctype="multipart/form-data"
                                                  action="{{ url(LANG . '/back/goods/shades/saveimg/' . $one_item->id) }}"
                                                  class="d-inline-flex gap-1 align-items-center">
                                                @csrf
                                                <input type="file" name="shade_photo" accept="image/*"
                                                       class="form-control form-control-sm" style="max-width: 220px;"
                                                       required>
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    {{ $one_item->shade_img ? __('variables.shades_replace') : __('variables.shades_upload') }}
                                                </button>
                                            </form>
                                            @if($one_item->shade_img)
                                                <form method="POST"
                                                      action="{{ url(LANG . '/back/goods/shades/deleteimg/' . $one_item->id) }}"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        {{ __('variables.shades_delete_photo') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        @include('admin.templates.pagination', ['paginator' => $shades_list])
                    @else
                        @include('admin.templates.empty-list')
                    @endif

                </div>
            </div>
        </div>
    </div>

@endsection
