@extends('admin.app')

@section('content')

    <div class="page-wrapper">
        <div class="page-content">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">{{ __('variables.total_orders') }}</p>
                                    <h4 class="my-1 text-info">{{ $orders ? $orders->count() : 0 }}</h4>
                                    <p class="mb-0 font-13">{{ $orders_ago_week ?? 0 }} {{ __('variables.from_last_week') }}</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                                    <i
                                        class='bx bxs-cart'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">{{ __('variables.total_revenue') }}</p>
                                    <h4 class="my-1 text-danger">{{ getDefaultPriceFormat($orders_total_price) }}</h4>
                                    <p class="mb-0 font-13">{{ getDefaultPriceFormat($orders_total_price_ago_week) }}
                                        {{ __('variables.from_last_week') }}</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto"><i
                                        class='bx bxs-wallet'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">{{ __('variables.total_customers') }}</p>
                                    <h4 class="my-1 text-warning">{{ $front_users ? $front_users->count() : 0 }}</h4>
                                    <p class="mb-0 font-13">{{ $front_users_ago_week ?? 0 }} {{ __('variables.from_last_week') }}</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto">
                                    <i
                                        class='bx bxs-group'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 border-start border-0 border-3 border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">{{ __('variables.total_feedback') }}</p>
                                    <h4 class="my-1 text-success">{{ $feedback ? $feedback->count() : 0 }}</h4>
                                    <p class="mb-0 font-13">{{ $feedback_ago_week ?? 0 }} {{ __('variables.from_last_week') }}</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                    <i class='bx bx-comment-detail'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10 w-100">
                        <div class="card-header bg-transparent">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ __('variables.recent_registered_users') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if(!empty($front_users) && count($front_users))
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th class="text-center">№.:</th>
                                                <th class="text-center">{{ __('variables.last_name') }}, {{ __('variables.name_text') }}</th>
                                                <th class="text-center">{{ __('variables.email') }}</th>
                                                <th class="text-center">{{ __('variables.phone') }}</th>
                                                <th class="text-center">{{ __('variables.total_orders_placed') }}</th>
                                                <th class="text-center">{{__('variables.confirmation')}}</th>
                                                <th class="text-center">{{__('variables.date_table')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($front_users->take(6) as $one_front_user)
                                                <tr>
                                                    <td class="text-center">{{ $one_front_user->id }}</td>
                                                    <td class="text-center">
                                                        <span>{{$one_front_user->last_name . ' ' . $one_front_user->name ?? '' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span><a
                                                                href="mailto:{{ $one_front_user->email ?? '' }}">{{ $one_front_user->email ?? '' }}</a></span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($one_front_user->phone)
                                                            <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', $one_front_user->phone) }}">{{$one_front_user->phone ?? __('variables.do_not_exist')}}</a>
                                                        @else
                                                            <span>{{ __('variables.do_not_exist') }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{ $one_front_user->frontUserTotalOrders->count() ?? 0 }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($one_front_user->confirmation == 1)
                                                            <span class="badge bg-success">{{ __('variables.user_confirm') }}</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ __('variables.user_no_confirm') }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_front_user->created_at) }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 d-flex">
                    <div class="card radius-10 w-100">
                        <div class="card-header bg-transparent">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ __('variables.recent_orders') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if(!empty($orders) && count($orders))
                                    <div class="table-responsive">
                                        <table class="table mb-0 table-hover">
                                            <thead>
                                            <tr>
                                                <th scope="col" class="text-center">№.:</th>
                                                <th scope="col" class="text-center">{{__('variables.order_type')}}</th>
                                                <th scope="col" class="text-center">{{__('variables.last_name')}}
                                                    , {{__('variables.name_text')}}</th>
                                                <th scope="col" class="text-center">{{__('variables.email_text')}}</th>
                                                <th scope="col" class="text-center">{{__('variables.phone')}}</th>
                                                <th scope="col" class="text-center">{{__('variables.total_count')}}</th>
                                                <th scope="col" class="text-center">{{__('variables.total_price')}}</th>
                                                <th scope="col"
                                                    class="text-center">{{__('variables.delivery_method')}}</th>
                                                <th scope="col" class="text-center">{{__('variables.pay_method')}}</th>
                                                <th scope="col" class="text-center">{{__('variables.date_table')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($orders->take(6) as $one_order)
                                                @php
                                                    if($one_order->ordersFrontUser)
                                                        $user_info = $one_order->ordersFrontUser;
                                                    else
                                                        $user_info = $one_order->ordersUsers;
                                                @endphp
                                                <tr class="row-id" data-id="{{$one_order->id}}">
                                                    <th class="text-center" scope="row">{{ $one_order->id }}</th>
                                                    <td class="text-center">
                                                        @if($one_order->fast_order == 1)
                                                            <span class="badge bg-info"
                                                                  style="font-size: 12px;">Fast</span>
                                                        @else
                                                            <span class="badge bg-primary" style="font-size: 12px;">Simple</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{$user_info->name .' '.$user_info->last_name ?? __('variables.do_not_exist')}}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($user_info->email)
                                                            <a href="mailto:{{ $user_info->email ?? '' }}">{{ $user_info->email ?? '' }}</a>
                                                        @else
                                                            <span>{{ __('variables.do_not_exist') }}</span>
                                                        @endif

                                                    </td>
                                                    <td class="text-center">
                                                        <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', $user_info->phone) }}">{{$user_info->phone ?? __('variables.do_not_exist')}}</a>
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{$one_order->ordersData->total_count ?? __('variables.do_not_exist')}}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{$one_order->ordersData->total_price ?? __('variables.do_not_exist')}}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{$one_order->delivery_method ?? ''}}
                                                            @if($one_order->delivery_method == 'delivery')
                                                                <span
                                                                    class="badge bg-secondary">({{$one_order->ordersData->delivery_cost ?? 0}})</span>
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{$one_order->pay_method ?? ''}}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_order->created_at) }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 d-flex">
                    <div class="card w-100 radius-10">
                        <div class="card-header bg-transparent">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ __('variables.recent_feedback') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(!empty($feedback) && count($feedback))
                                <div class="card radius-10 border shadow-none">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="table-responsive">
                                                <table class="table mb-0 table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th scope="col" class="text-center">№</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.name_text')}}</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.email_text')}}</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.phone')}}</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.comment_table')}}</th>
                                                        <th scope="col"
                                                            class="text-center">{{__('variables.date_table')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($feedback->take(6) as $one_feedform)
                                                        <tr class="row-id" data-id="{{$one_feedform->id}}">
                                                            <th class="text-center"
                                                                scope="row">{{ $loop->iteration }}</th>
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
                                                                <span
                                                                    class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_feedform->created_at) }}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10 w-100">
                        <div class="card-header bg-transparent">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ __('variables.recent_added_goods') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if(!empty($goods_items) && count($goods_items))
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th class="text-center">№.:</th>
                                                <th class="text-center">{{__('variables.photo')}}</th>
                                                <th class="text-center">{{__('variables.title_table')}}</th>
                                                <th class="text-center">{{__('variables.1c_code')}}</th>
                                                <th class="text-center">{{__('variables.articol')}}</th>
                                                <th class="text-center">{{__('variables.barcode')}}</th>
                                                <th class="text-center">{{__('variables.price')}}</th>
                                                <th class="text-center">{{__('variables.price_promo')}}</th>
                                                <th class="text-center">{{__('variables.availability')}}</th>
                                                <th class="text-center">{{__('variables.details')}}</th>
                                                <th class="text-center">{{__('variables.date_table')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($goods_items->take(6) as $one_goods_item)
                                                @php
                                                    $goods_price_collect = getGoodsPrice($one_goods_item);
                                                    $goods_subject_alias = $one_goods_item->getSubjectId ? $one_goods_item->getSubjectId->alias : null;
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $one_goods_item->id }}</td>
                                                    <td class="text-center">
                                                        @if(!empty($one_goods_item->oImage) && file_exists('upfiles/goods-items/' . $one_goods_item->oImage->img))
                                                            <a href="{{ asset('upfiles/goods-items') }}/{{$one_goods_item->oImage->img ?? ''}}"
                                                               data-fancybox="">
                                                                <img
                                                                    src="{{ asset('upfiles/goods-items/m') }}/{{ showImg($one_goods_item->oImage->img) ?? ''}}"
                                                                    class="product-sm-img">
                                                            </a>
                                                        @else
                                                            <img src="{{asset('admin-assets/images/no-image.png')}}"
                                                                 alt="no-image" class="product-sm-img"
                                                                 title="No image">
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{ $one_goods_item->itemByLang->name ?? ''}}</span><br>
                                                        <span class="text-secondary font-13">{{__('variables.product_category')}}
                                                            - {{ $one_goods_item->itemByLang->subject_nav_name ?? '' }}</span><br>
                                                        <span class="text-secondary font-13">{{__('variables.brand')}}
                                                            - {{ $one_goods_item->itemByLang->brand_nav_name ?? '' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{ $one_goods_item->one_c_code ?? '' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{ $one_goods_item->articol ?? '' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{ $one_goods_item->barcode ?? '' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{ $goods_price_collect->price ?? '' }}MDL</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="text-danger">{{ $goods_price_collect->price_promo ?? '' }}
                                                            MDL</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($one_goods_item->in_stoc > 0)
                                                            <span class="badge bg-success">{{__('variables.in_stock')}}</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ __('variables.not_in_stock') }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ url(LANG, ['back', 'goods', $goods_subject_alias, 'editgoodsitem', $one_goods_item->id, LANG_ID]) }}"
                                                           class="text-decoration-underline">{{ __('variables.to_go') }}</a>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge bg-secondary">{{ getDefaultDateFormatAdmin($one_goods_item->created_at) }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-header bg-transparent">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ __('variables.recent_gallery_items') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div>
                                @if(!is_null($gallery_subject))
                                    <p class="mb-0 text-secondary">
                                        <a href="{{url(LANG, ['back', 'gallery', $gallery_subject_id->alias, 'memberslist'])}}">{{$gallery_subject->name}}</a>
                                    </p>
                                @endif
                                <p class="mb-0 font-13">{{ __('variables.gallery_items') }}
                                    : {{$count_gallery_items}}</p>
                            </div>

                            <div class="row mb-3 row-cols-auto g-2 mt-3">
                                @if(!empty($gallery_item) && count($gallery_item))
                                    @foreach($gallery_item as $item)
                                        <div class="col">
                                            @if($item->galleryItemId->type == 'photo')
                                                @if(!empty($item->galleryItemId->img) && file_exists('upfiles/gallery-items/' . $item->galleryItemId->img))
                                                    <a href="{{ asset('upfiles/gallery-items') }}/{{ $item->galleryItemId->img }}"
                                                       data-fancybox="gallery">
                                                        <img
                                                            src="{{ asset('upfiles/gallery-items/m') }}/{{showImg($item->galleryItemId->img) ?? ''}}"
                                                            class="rounded p-1 border" width="100" height="100">
                                                    </a>
                                                @else
                                                    <img src="{{asset('admin-assets/images/no-image.png')}}"
                                                         alt="no-image" width="100" height="100"
                                                         title="No image">
                                                @endif
                                            @else
                                                <a href="https://www.youtube.com/embed/{{$item->galleryItemId->youtube_id ?? '' }}?autoplay=0"
                                                   data-fancybox="main-gallery">
                                                    <img
                                                        src="http://img.youtube.com/vi/{{$item->galleryItemId->youtube_id ?? '' }}/0.jpg"
                                                        alt="{{$item->name ?? '' }}" class="rounded p-1 border"
                                                        width="100" height="100">
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@stop
