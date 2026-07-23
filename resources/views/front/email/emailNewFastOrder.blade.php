<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letter</title>
</head>
<body>

<table
    style="width: 100%; max-width: 1000px; height: auto; max-height: 100000000000px; border-spacing: 0; border-collapse: collapse; font-family: Arial, sans-serif; margin: 0 auto; padding: 0;">
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
                        style="border: 1px solid #dbdbdb; padding: 15px;">{{ ShowLabelById(84) }}
                    </th>
                </tr>
                <tr>
                    <td style="border: 1px solid #dbdbdb; padding: 15px; width: 50%;">
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(70) }}:</span> №{{ $orders->id ?? '' }}</p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(85) }}:</span> {{ getDefaultDateFormat($orders->created_at) }}</p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(253) }}:</span> {{ ShowLabelById(250) }}</p>
                    </td>
                    <td style="border: 1px solid #dbdbdb; padding: 15px; width: 50%;">
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(41) }}:</span> {{ $user_info->phone ?? '' }}</p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(35) }}:</span> {{ $user_info->last_name ?? '' }}</p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(36) }}:</span> {{ $user_info->name ?? '' }}</p>
                        <p style="margin: 0;"><span style="font-weight: bold;">{{ ShowLabelById(257) }}:</span> {{ $orders_users->user_ip ?? '' }}</p>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 15px 0;">
            @if($basket)
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
                    <tr>
                        <td style="border: 1px solid #dbdbdb; padding: 15px;">
                            <p style="margin: 0;">1</p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px;">
                            <p style="margin: 0;">
                                <a href="{{ route('catalog-product', ['product', $basket->goodsItemId->alias]) }}">
                                    <img
                                        src="{{ $basket->goodsItemId->oImage && $basket->goodsItemId->oImage->img && file_exists('upfiles/goods-items/s/' . showImg($basket->goodsItemId->oImage->img)) ? asset('upfiles/goods-items/s/'. showImg($basket->goodsItemId->oImage->img)) : asset('front-assets/img/no-image-xs.png') }}"
                                        width="50px" height="50px"
                                        alt="{{ $basket->goodsItemId->itemByLang->name ?? '' }}">
                                </a>
                            </p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px;">
                            <p style="margin: 0;"><a style="text-decoration: none; color: #000000"
                                                     href="{{ route('catalog-product', ['product', $basket->goodsItemId->alias]) }}">{{ $basket->goodsItemId->itemByLang->name ?? '' }}</a>
                            </p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0;">{{ getDefaultPriceFormat($basket->goods_price) }} {{ ShowLabelById(3) }}</p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0;">{{ $basket->items_count ?? '' }}</p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0;">
                                {{ getDefaultPriceFormat($basket->goods_price * $basket->items_count) }} {{ ShowLabelById(3) }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0; font-weight: bold;">{{ ShowLabelById(23) }}:</p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0;">{{ getDefaultPriceFormat($orders_data->total_price) }}
                                {{ ShowLabelById(3) }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0; font-weight: bold;">{{ ShowLabelById(67) }}:</p>
                        </td>
                        <td style="border: 1px solid #dbdbdb; padding: 15px; text-align: right;">
                            <p style="margin: 0;">
                                {{ getDefaultPriceFormat($orders_data->delivery_cost + $orders_data->total_price) }}
                                {{ ShowLabelById(3) }}</p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            @endif
        </td>
    </tr>
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
