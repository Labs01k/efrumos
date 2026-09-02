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
                        <h5 class="mb-0">
                            {{ __('variables.shades_report') }}
                            @if($brand_name)
                                — {{ $brand_name }}
                            @endif
                        </h5>
                        <div class="ms-auto">
                            <a href="{{ url(LANG . '/back/goods/shades/massupload') }}"
                               class="btn btn-primary mt-2 mt-lg-0">
                                <i class="bx bx-upload"></i> {{ __('variables.shades_mass_upload') }}
                            </a>
                            <a href="{{ url(LANG . '/back/goods/shades') }}" class="btn btn-primary mt-2 mt-lg-0">
                                <i class="lni lni-list"></i> {{ __('variables.shades_back_to_list') }}
                            </a>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6 col-md-3">
                            <div class="alert alert-success mb-0 text-center">
                                <b>{{ count($report['saved']) }}</b><br>{{ __('variables.shades_saved_count') }}
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="alert alert-info mb-0 text-center">
                                <b>{{ count($report['replaced']) }}</b><br>{{ __('variables.shades_replaced_count') }}
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="alert alert-warning mb-0 text-center">
                                <b>{{ count($report['ambiguous']) }}</b><br>{{ __('variables.shades_ambiguous') }}
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="alert alert-danger mb-0 text-center">
                                <b>{{ count($report['unmatched']) }}</b><br>{{ __('variables.shades_unmatched') }}
                            </div>
                        </div>
                    </div>

                    @foreach(['saved' => 'alert-success', 'replaced' => 'alert-info'] as $section => $css)
                        @if(count($report[$section]))
                            <h6>{{ __('variables.shades_' . $section . '_count') }}</h6>
                            <table class="table table-sm mb-4">
                                @foreach($report[$section] as $one_row)
                                    <tr>
                                        <td style="width: 30%;"><code>{{ $one_row['file'] }}</code></td>
                                        <td>{{ $one_row['item']->itemByLang->name ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    @endforeach

                    @if(count($report['ambiguous']))
                        <h6>{{ __('variables.shades_ambiguous') }}</h6>
                        <table class="table table-sm mb-4">
                            @foreach($report['ambiguous'] as $one_row)
                                <tr>
                                    <td style="width: 30%;"><code>{{ $one_row['file'] }}</code></td>
                                    <td>
                                        @foreach($one_row['items'] as $one_candidate)
                                            {{ $one_candidate->itemByLang->name ?? '' }}@if(!$loop->last), @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @endif

                    @if(count($report['unmatched']))
                        <h6>{{ __('variables.shades_unmatched') }}</h6>
                        <p>
                            @foreach($report['unmatched'] as $one_file)
                                <code>{{ $one_file }}</code>@if(!$loop->last), @endif
                            @endforeach
                        </p>
                    @endif

                </div>
            </div>
        </div>
    </div>

@endsection
