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
                        <h5 class="mb-0">{{ __('variables.purpose_import_report_title') }}</h5>
                        <div class="ms-auto">
                            <a href="{{ url(LANG . '/back/goods/goods-purpose-import') }}"
                               class="btn btn-primary mt-2 mt-lg-0">
                                <i class="bx bx-upload"></i> {{ __('variables.purpose_import_title') }}
                            </a>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6 col-md-3">
                            <div class="alert alert-success mb-0 text-center">
                                <b>{{ count($report['saved']) }}</b><br>{{ __('variables.purpose_import_saved') }}
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="alert alert-info mb-0 text-center">
                                <b>{{ count($report['replaced']) }}</b><br>{{ __('variables.purpose_import_replaced') }}
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="alert alert-warning mb-0 text-center">
                                <b>{{ count($report['unmatched_value']) }}</b><br>{{ __('variables.purpose_import_unmatched_value') }}
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="alert alert-danger mb-0 text-center">
                                <b>{{ count($report['unmatched_product']) }}</b><br>{{ __('variables.purpose_import_unmatched_product') }}
                            </div>
                        </div>
                    </div>

                    @foreach(['saved' => 'purpose_import_saved', 'replaced' => 'purpose_import_replaced'] as $section => $label)
                        @if(count($report[$section]))
                            <h6>{{ __('variables.' . $label) }}</h6>
                            <table class="table table-sm mb-4">
                                @foreach($report[$section] as $one_row)
                                    <tr>
                                        <td style="width: 15%;"><code>{{ $one_row['articol'] }}</code></td>
                                        <td style="width: 35%;">{{ $one_row['item']->itemByLang->name ?? '' }}</td>
                                        <td>{{ implode(', ', $one_row['values']) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    @endforeach

                    @if(count($report['unmatched_value']))
                        <h6>{{ __('variables.purpose_import_unmatched_value') }}</h6>
                        <table class="table table-sm mb-4">
                            @foreach($report['unmatched_value'] as $one_row)
                                <tr>
                                    <td style="width: 15%;"><code>{{ $one_row['articol'] }}</code></td>
                                    <td style="width: 35%;">{{ $one_row['item']->itemByLang->name ?? '' }}</td>
                                    <td class="text-danger">{{ implode(', ', $one_row['unknown']) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif

                    @if(count($report['unmatched_product']))
                        <h6>{{ __('variables.purpose_import_unmatched_product') }}</h6>
                        <p>
                            @foreach($report['unmatched_product'] as $one_row)
                                <code>{{ $one_row['articol'] }}</code>@if(!$loop->last), @endif
                            @endforeach
                        </p>
                    @endif

                </div>
            </div>
        </div>
    </div>

@endsection
