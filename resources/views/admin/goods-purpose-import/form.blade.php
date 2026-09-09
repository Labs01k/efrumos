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
                        <h5 class="mb-0">{{ __('variables.purpose_import_title') }}</h5>
                    </div>

                    @if(session('purpose_error'))
                        <div class="alert alert-danger">{{ session('purpose_error') }}</div>
                    @endif

                    <div class="alert alert-info">
                        {{ __('variables.purpose_import_hint') }}
                        <br>
                        <a href="{{ asset('examples/purpose-import-example.csv') }}" download>
                            <i class="bx bx-download"></i> {{ __('variables.purpose_import_example_download') }}
                        </a>
                    </div>

                    <form method="POST" enctype="multipart/form-data"
                          action="{{ url(LANG . '/back/goods/goods-purpose-import/savemass') }}" class="row g-3">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label">{{ __('variables.purpose_import_file') }}</label>
                            <input type="file" name="purpose_csv" accept=".csv,.txt" class="form-control" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">{{ __('variables.purpose_import_submit') }}</button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <h6>{{ __('variables.purpose_import_known_values_title') }}</h6>
                    <p class="text-muted">{{ __('variables.purpose_import_known_values_hint') }}</p>
                    <div class="row">
                        @foreach($known_values as $one_value)
                            <div class="col-md-4"><code>{{ $one_value }}</code></div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
