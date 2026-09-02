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
                        <h5 class="mb-0">{{ __('variables.shades_mass_upload') }}</h5>
                        <div class="ms-auto">
                            <a href="{{ url(LANG . '/back/goods/shades') }}" class="btn btn-primary mt-2 mt-lg-0">
                                <i class="lni lni-list"></i> {{ __('variables.shades_back_to_list') }}
                            </a>
                        </div>
                    </div>

                    @if(session('shade_error'))
                        <div class="alert alert-danger">{{ session('shade_error') }}</div>
                    @endif

                    <div class="alert alert-info">{{ __('variables.shades_mass_hint') }}</div>

                    <form method="POST" enctype="multipart/form-data"
                          action="{{ url(LANG . '/back/goods/shades/savemass') }}" class="row g-3">
                        @csrf
                        <div class="col-md-5">
                            <label class="form-label">{{ __('variables.shades_choose_line') }}</label>
                            <select name="brand" class="form-select">
                                <option value="">{{ __('variables.shades_all_lines') }}</option>
                                @foreach($brand_list as $one_brand)
                                    <option value="{{ $one_brand->id }}">{{ $one_brand->itemByLang->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">{{ __('variables.shades_photo') }}</label>
                            <input type="file" name="shade_photos[]" accept="image/*" multiple
                                   class="form-control" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">{{ __('variables.shades_upload') }}</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection
