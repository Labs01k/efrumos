<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letter</title>
</head>
<body>

<table style="width: 100%; max-width: 1000px; height: auto; max-height: 100000000000px; border-spacing: 0; border-collapse: collapse; font-family: Arial, sans-serif; margin: 0 auto; padding: 0;">
    <tbody>
    <tr>
        <td style="padding: 15px 0;">
            <img style="margin: 0 auto; width: 140px;" src="{{asset('front-assets/img/logo/logo.png')}}"
                 alt="Efrumos Logo">
        </td>
    </tr>
    @if($email_message && $email_message->itemByLang && $email_message->itemByLang->body)
        <tr>
            <td>
                {!! $email_message->itemByLang->body ?? '' !!}
            </td>
        </tr>
    @endif
    <tr>
        <td style="padding: 15px 0;">
            <table style="width: 100%; border-spacing: 0; border-collapse: collapse;">
                <tbody>
                <tr style="text-align: left; background-color: #efefef;">
                    <th colspan="2"
                        style="border: 1px solid #dbdbdb; padding: 15px;">{{ ShowLabelById(84) }}</th>
                </tr>
                <tr>
                    <td style="border: 1px solid #dbdbdb; padding: 15px; width: 50%;">
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(70) }}:</span> №{{ $orders->id ?? '' }}</p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(85) }}:</span> {{ getDefaultDateFormat($orders->created_at) }}</p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(218) }}:</span> {{ getEnumValueName($orders->pay_method) }}</p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(212) }}:</span> {{ getEnumValueName($orders->delivery_method) }}</p>
                    </td>
                    <td style="border: 1px solid #dbdbdb; padding: 15px; width: 50%;">
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(34) }}:</span> <a href="mailto:{{ $user_info->email ?? '' }}">{{ $user_info->email ?? '' }}</a></p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(41) }}:</span> {{ $user_info->phone ?? '' }}</p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(257) }}:</span> {{ $orders_users->user_ip ?? '' }}</p>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 15px 0;">
            <table style="width: 100%; border-spacing: 0; border-collapse: collapse;">
                <tbody>
                <tr style="text-align: left; background-color: #efefef;">
                    <th style="border: 1px solid #dbdbdb; padding: 15px; width: 50%;">{{ ShowLabelById(258) }}</th>
                    <th style="border: 1px solid #dbdbdb; padding: 15px; width: 50%;">{{ ShowLabelById(259) }}</th>
                </tr>
                <tr>
                    <td style="border: 1px solid #dbdbdb; padding: 15px; width: 50%;">
                        <p style="margin: 0;">{{ $user_info->last_name ?? '' }} {{ $user_info->name ?? '' }}</p>
                        <p style="margin: 0;">{{ $user_district->name ?? '' }}</p>
                        <p style="margin: 0;">{{ $user_info->city ?? '' }}</p>
                        <p style="margin: 0;">{{ $user_info->address ?? '' }}</p>
                    </td>
                    <td style="border: 1px solid #dbdbdb; padding: 15px; width: 50%;">
                        <p style="margin: 0;">{{ $user_info->last_name ?? '' }} {{ $user_info->name ?? '' }}</p>
                        <p style="margin: 0;">{{ $user_district->name ?? '' }}</p>
                        <p style="margin: 0;">{{ $user_info->city ?? '' }}</p>
                        <p style="margin: 0;">{{ $user_info->address ?? '' }}</p>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 15px 0;">
            @if(!empty($basket) && count($basket))
                <table style="width: 100%; border-spacing: 0; border-collapse: collapse;">
                    <tbody>
                    <tr style="text-align: left; background-color: #efefef;">
                        <th style="border: 1px solid #dbdbdb; padding: 15px;">№</th>
                        <th style="border: 1px solid #dbdbdb; padding: 15px;">{{ ShowLabelById(254) }}</th>
                        <th style="border: 1px solid #dbdbdb; padding: 15px;">{{ ShowLabelById(79) }}</th>
                        <th style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">{{ ShowLabelById(119) }}</th>
                        <th style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">{{ ShowLabelById(80) }}</th>
                        <th style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">{{ ShowLabelById(81) }}</th>
                    </tr>
                    @foreach($basket as $one_basket_item)
                        <tr>
                            <td style="border: 1px solid #dbdbdb; padding: 15px;">
                                <p style="margin: 0;">{{ $loop->iteration ?? '' }}</p>
                            </td>
                            <td style="border: 1px solid #dbdbdb; padding: 15px;">
                                <p style="margin: 0;">
                                    <a href="{{ route('catalog-product', ['product', $one_basket_item->goodsItemId->alias]) }}">
                                        <img
                                            src="{{ $one_basket_item->goodsItemId->oImage && $one_basket_item->goodsItemId->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($one_basket_item->goodsItemId->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($one_basket_item->goodsItemId->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}"  width="50px" height="50px"
                                            alt="{{ $one_basket_item->goodsItemId->itemByLang->name ?? '' }}">
                                    </a>
                                </p>
                            </td>
                            <td style="border: 1px solid #dbdbdb; padding: 15px;">
                                <p style="margin: 0;"><a style="text-decoration: none; color: #000000" href="{{ route('catalog-product', ['product', $one_basket_item->goodsItemId->alias]) }}">{{ $one_basket_item->goodsItemId->itemByLang->name ?? '' }}</a></p>
                            </td>
                            <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                                <p style="margin: 0;">{{ getDefaultPriceFormat($one_basket_item->goods_price) }} {{ ShowLabelById(3) }}</p>
                            </td>
                            <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                                <p style="margin: 0;">{{ $one_basket_item->items_count ?? '' }}</p>
                            </td>
                            <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                                <p style="margin: 0;">
                                    {{ getDefaultPriceFormat($one_basket_item->goods_price * $one_basket_item->items_count) }} {{ ShowLabelById(3) }}</p>
                            </td>
                        </tr>
                        @if($one_basket_item->has_cadou == 1 && $one_basket_item->related_one_c_id > 0)
                            <tr>
                                <td style="border-left: 1px solid #dbdbdb; padding: 15px;">
                                    <p style="margin: 0;">
                                        <a href="{{ route('catalog-product', ['product', $cadou->alias]) }}">
                                            <img
                                                src="{{ $cadou->oImage && $cadou->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($cadou->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($cadou->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}" width="50px" height="50px"
                                                alt="{{ $cadou->itemByLang->name ?? '' }}">
                                        </a>
                                    </p>
                                </td>
                                <td style="padding: 15px;text-align: left;">
                                    <p><span style="color: #E47F9E; font-weight: bold">{{ ShowLabelById(260) }}</span> {{ $cadou->itemByLang->name ?? '' }}</p>
                                </td>
                                <td style="padding: 15px;">
                                </td>
                                <td style="padding: 15px;">
                                </td>
                                <td style="padding: 15px;">
                                </td>
                                <td style="border-right: 1px solid #dbdbdb;padding: 15px">
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    <tr>
                        <td colspan="5" style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0; font-weight: bold;">{{ ShowLabelById(23) }}:</p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0;">{{ getDefaultPriceFormat($discount_goods_price && $discount_goods_price > 0 ? $orders_data->total_price + $discount_goods_price : $orders_data->total_price) }} {{ ShowLabelById(3) }}</p>
                        </td>
                    </tr>
                    @if($discount_goods_price && $discount_goods_price > 0)
                        <tr>
                            <td colspan="5" style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                                <p style="margin: 0; font-weight: bold;">{{ ShowLabelById(255) }}:</p>
                            </td>
                            <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                                <p style="margin: 0;">{{ getDefaultPriceFormat($discount_goods_price) }} {{ ShowLabelById(3) }}</p>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="5" style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0; font-weight: bold;">{{ ShowLabelById(207) }}:</p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0;">{{ getDefaultPriceFormat($orders_data->delivery_cost) }} {{ ShowLabelById(3) }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0; font-weight: bold;">{{ ShowLabelById(67) }}:</p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0;">
                                {{ getDefaultPriceFormat($orders_data->delivery_cost + $orders_data->total_price) }} {{ ShowLabelById(3) }}</p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            @endif
        </td>
    </tr>
    @if($orders_users->descr)
        <tr>
            <td style="padding: 15px 0;">
                <table style="width: 100%; border-spacing: 0; border-collapse: collapse;">
                    <tbody>
                    <tr style="text-align: left; background-color: #efefef;">
                        <th style="border: 1px solid #dbdbdb; padding: 15px;">{{ ShowLabelById(256) }}</th>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #dbdbdb; padding: 15px;">
                            <p style="margin: 0;">{{ $orders_users->descr ?? '' }}</p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    @endif
     @if($email_message && $email_message->itemByLang && $email_message->itemByLang->body_two)
         <tr>
             <td>
                 {!! $email_message->itemByLang->body_two ?? '' !!}
             </td>
         </tr>
     @endif
    </tbody>
</table>
</body>
</html>
