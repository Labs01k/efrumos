@extends('admin.app')

@push('after-head-scripts')
    <script>
        window.dataLayer = window.dataLayer || [];
    </script>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-PLD5TQR');</script>
    <!-- End Google Tag Manager -->
@endpush

@section('content')
    @push('after-body-start-scripts')
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PLD5TQR"
                          height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endpush

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
                            <h6 class="mb-0 text-uppercase">Order info</h6>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ urlForFunctionLanguage($lang, '') }}"
                               class="btn btn-primary mt-2 mt-lg-0"><i
                                    class="lni lni-list"></i>{{ __('variables.elements_list') }}</a>
                            <a href="{{ urlForFunctionLanguage($lang, 'ordersCart/orderscart') }}"
                               class="btn btn-primary mt-2 mt-lg-0"><i
                                    class="lni lni-trash"></i>{{ __('variables.elements_basket') }}
                            </a>
                        </div>
                    </div>

                    {{-- Epic 1 / 1.4 — manual payment status override. Current value comes
                         from Orders::$casts (App\Enums\PaymentStatus), so it always reflects
                         what OrderPaymentStatusService last set, automatically or manually. --}}
                    <div class="border rounded p-3 mb-3 d-lg-flex align-items-center gap-3">
                        <div>
                            <strong>{{ __('variables.payment_status') }}:</strong>
                            <span id="payment-status-current">{{ $orders->payment_status?->label() ?? __('variables.payment_status_pending') }}</span>
                        </div>
                        <div class="ms-lg-auto d-flex align-items-center gap-2 mt-2 mt-lg-0">
                            <select id="payment-status-select" class="form-select form-select-sm" style="width:auto;">
                                @foreach (\App\Enums\PaymentStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($orders->payment_status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <input id="payment-status-comment" type="text" class="form-control form-control-sm"
                                   placeholder="{{ __('variables.payment_status_comment_placeholder') }}" style="width:220px;">
                            <button type="button" id="payment-status-save" class="btn btn-sm btn-primary">{{ __('variables.payment_status_save') }}</button>
                            @if ($orders->payment_status === \App\Enums\PaymentStatus::Paid && $orders->pay_method === 'card')
                                <button type="button" id="payment-refund" class="btn btn-sm btn-outline-danger">{{ __('variables.payment_refund_button') }}</button>
                            @endif
                        </div>
                    </div>

                    @if ($payment_status_logs->isNotEmpty())
                        <div class="border rounded p-3 mb-3">
                            <strong>{{ __('variables.payment_status_history') }}</strong>
                            <table class="table table-sm mb-0 mt-2">
                                <thead>
                                <tr>
                                    <th>{{ __('variables.payment_status_history_date') }}</th>
                                    <th>{{ __('variables.payment_status_history_from') }}</th>
                                    <th>{{ __('variables.payment_status_history_to') }}</th>
                                    <th>{{ __('variables.payment_status_history_source') }}</th>
                                    <th>{{ __('variables.payment_status_history_admin') }}</th>
                                    <th>{{ __('variables.payment_status_history_comment') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($payment_status_logs as $log)
                                    <tr>
                                        <td>{{ getDefaultDateFormat($log->created_at) }}</td>
                                        <td>{{ $log->from_status ? \App\Enums\PaymentStatus::from($log->from_status)->label() : '—' }}</td>
                                        <td>{{ \App\Enums\PaymentStatus::from($log->to_status)->label() }}</td>
                                        <td>{{ $log->source }}</td>
                                        <td>{{ $log->changedByAdmin->name ?? '—' }}</td>
                                        <td>{{ $log->comment ?? '—' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <script>
                        document.getElementById('payment-status-save').addEventListener('click', function () {
                            // form-urlencoded, not JSON — this app's request pipeline
                            // does not parse a JSON body correctly (confirmed while
                            // testing this widget), and it matches how the existing
                            // changeActive AJAX calls in custom.js already post data.
                            var body = new URLSearchParams({
                                id: {{ $orders->id }},
                                payment_status: document.getElementById('payment-status-select').value,
                                comment: document.getElementById('payment-status-comment').value,
                            });
                            fetch('{{ urlForFunctionLanguage($lang, 'orders/changePaymentStatus') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: body,
                            })
                                .then(r => {
                                    if (r.status === 419) throw new Error('Сессия/CSRF-токен устарели — обновите страницу (F5) и попробуйте снова.');
                                    if (!r.ok) throw new Error('Сервер вернул ошибку ' + r.status);
                                    return r.json();
                                })
                                .then(response => {
                                    if (response.status === true) {
                                        document.getElementById('payment-status-current').textContent = response.text;
                                        document.getElementById('payment-status-comment').value = '';
                                        if (window.Notiflix) {
                                            Notiflix.Notify.info(response.messages[0]);
                                        } else {
                                            alert(response.messages[0]);
                                        }
                                        // reload so the history table below (server-rendered) picks up the new row
                                        setTimeout(() => location.reload(), 800);
                                    } else {
                                        const msg = (response.messages && response.messages[0]) || 'Error';
                                        if (window.Notiflix) {
                                            Notiflix.Notify.failure(msg);
                                        } else {
                                            alert(msg);
                                        }
                                    }
                                })
                                .catch(err => {
                                    console.error('changePaymentStatus failed:', err);
                                    if (window.Notiflix) {
                                        Notiflix.Notify.failure(err.message);
                                    } else {
                                        alert(err.message);
                                    }
                                });
                        });

                        const refundBtn = document.getElementById('payment-refund');
                        if (refundBtn) {
                            refundBtn.addEventListener('click', function () {
                                if (!confirm('{{ __('variables.payment_refund_confirm') }}')) return;
                                fetch('{{ urlForFunctionLanguage($lang, 'orders/refundPayment') }}', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: new URLSearchParams({ id: {{ $orders->id }} }),
                                })
                                    .then(r => {
                                        if (r.status === 419) throw new Error('Сессия/CSRF-токен устарели — обновите страницу (F5) и попробуйте снова.');
                                        if (!r.ok) throw new Error('Сервер вернул ошибку ' + r.status);
                                        return r.json();
                                    })
                                    .then(response => {
                                        const msg = (response.messages && response.messages[0]) || 'Error';
                                        if (response.status === true) {
                                            document.getElementById('payment-status-current').textContent = response.text;
                                            refundBtn.remove();
                                        }
                                        if (window.Notiflix) {
                                            response.status === true ? Notiflix.Notify.info(msg) : Notiflix.Notify.failure(msg);
                                        } else {
                                            alert(msg);
                                        }
                                        if (response.status === true) {
                                            setTimeout(() => location.reload(), 800);
                                        }
                                    })
                                    .catch(err => {
                                        console.error('refundPayment failed:', err);
                                        if (window.Notiflix) {
                                            Notiflix.Notify.failure(err.message);
                                        } else {
                                            alert(err.message);
                                        }
                                    });
                            });
                        }
                    </script>

                    <hr/>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-secondary">
                            <tr>
                                <th scope="col" class="text-center">№.:</th>
                                <th scope="col" class="text-center">{{__('variables.order_type')}}</th>
                                <th scope="col" class="text-center">{{__('variables.delivery_method')}}</th>
                                <th scope="col" class="text-center">{{__('variables.pay_method')}}</th>
                                <th scope="col" class="text-center">{{__('variables.total_count')}}</th>
                                <th scope="col" class="text-center">{{__('variables.total_price')}}</th>
                                <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                {{--@if($groupSubRelations->active == 1)
                                    <th scope="col" class="text-center">{{__('variables.active_table')}}</th>
                                @endif--}}
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="row-id" data-id="{{$orders->id ?? ''}}">
                                <th class="text-center" scope="row">{{ $orders->id }}</th>
                                <td class="text-center">
                                    <span>{{$orders->fast_order == 1 ? 'Fast' : 'Simple'}}</span>
                                </td>
                                <td class="text-center">
                                    <span>
                                        {{$orders->delivery_method ?? ''}}
                                        @if($orders->delivery_method == 'delivery')
                                            <span
                                                class="badge bg-secondary">({{$orders->ordersData->delivery_cost ?? 0}}
                                                )</span>
                                        @endif
                                        @if($orders->delivery_method == 'pickup' && $orders->pickupShop && $orders->pickupShop->itemByLang)
                                            <br><span class="badge bg-info">{{ $orders->pickupShop->itemByLang->name ?? '' }}</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span>{{$orders->pay_method ?? ''}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$orders->ordersData->total_count ?? ''}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$orders->ordersData->total_price ?? ''}}</span>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-secondary">{{ getDefaultDateFormatAdmin($orders->created_at) }}</span>
                                </td>
                                {{--<td class="text-center">
                                    <div class="form-switch">
                                        <input class="form-check-input change-active" type="checkbox"
                                               data-active="{{$orders->active}}"
                                               data-element-id="{{$orders->id}}"
                                               data-action="main-active"
                                               id="switch-active-{{$orders->id}}"
                                               data-url="{{$url_for_active_elem}}" {{$orders->active == 1 ? 'checked' : ''}}>
                                        <label class="form-check-label"
                                               for="switch-active-{{$orders->id}}"></label>
                                    </div>
                                </td>--}}
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="position-relative">
                            <h6 class="mb-0 text-uppercase">User info</h6>
                        </div>
                        <div class="ms-auto">
                        </div>
                    </div>
                    <hr/>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-secondary">
                            <tr>
                                <th scope="col" class="text-center">{{__('variables.name_text')}}</th>
                                <th scope="col" class="text-center">{{__('variables.last_name')}}</th>
                                <th scope="col" class="text-center">{{__('variables.email_text')}}</th>
                                <th scope="col" class="text-center">{{__('variables.phone')}}</th>
                                <th scope="col" class="text-center">{{__('variables.district_local')}}</th>
                                <th scope="col" class="text-center">{{__('variables.city')}}</th>
                                <th scope="col" class="text-center">{{__('variables.address')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="row-id">
                                <td class="text-center">
                                    <span>{{$user_info->name ?? ''}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->last_name ?? __('variables.do_not_exist')}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->email ?? __('variables.do_not_exist')}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->phone ?? __('variables.do_not_exist')}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_district->name ?? __('variables.do_not_exist') }}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->city ?? __('variables.do_not_exist')}}</span>
                                </td>
                                <td class="text-center">
                                    <span>{{$user_info->address ?? __('variables.do_not_exist')}}</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            @if(!empty($orderedItems) && count($orderedItems))
                <div class="card">
                    <div class="card-body">
                        <div class="d-lg-flex align-items-center mb-4 gap-3">
                            <div class="position-relative">
                                <h6 class="mb-0 text-uppercase">Ordered items</h6>
                            </div>
                            <div class="ms-auto">
                            </div>
                        </div>
                        <hr/>
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover">
                                <thead class="table-secondary">
                                <tr>
                                    <th scope="col" class="text-center">№</th>
                                    <th scope="col" class="text-center">{{__('variables.name_text')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.price')}} (1 шт.)</th>
                                    <th scope="col" class="text-center">{{__('variables.total_count')}}</th>
                                    <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($basket as $basket_elem)
                                    <tr class="row-id">
                                        <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-center">
                                            <span>{{$basket_elem->goods_name ?? ''}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$basket_elem->goods_price ?? ''}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span>{{$basket_elem->items_count ?? ''}}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-secondary">{{ getDefaultDateFormatAdmin($basket_elem->created_at) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if($orders->ordersUsers && $orders->ordersUsers->descr)
                <div class="card">
                    <div class="card-body">
                        <div class="d-lg-flex align-items-center mb-4 gap-3">
                            <div class="position-relative">
                                <h6 class="mb-0 text-uppercase">{{__('variables.comment_table')}}</h6>
                            </div>
                            <div class="ms-auto">
                            </div>
                        </div>
                        <hr/>
                        <div class="d-lg-flex align-items-center mb-4 gap-3">
                            <p>{{ $orders->ordersUsers->descr ?? '' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <button type="button" class="btn btn-outline-danger px-5 refund-order-ga4" data-order-id="{{ $orders->id ?? '' }}">{{__('variables.order_refund_ga4')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@stop

