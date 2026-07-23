<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Letter</title>
</head>
<body>
<table style="max-width: 1000px; height: auto; max-height: 100000000000px; border-spacing: 0; border-collapse: collapse; font-family: Arial,sans-serif; background-color: #C4E0FD; text-align: center; margin: 0 auto; padding: 0;" bgcolor="#636364">
    <tbody>
    <tr>
        <td style="width: 100%; padding: 10px 10px 0;">
            <table style="width: 100%; height: auto; max-height: 100000000000px; border-spacing: 0; border-collapse: collapse; font-family: Arial,sans-serif; margin: 0 auto;">
                <tbody>
                <tr style="background-color: #fff; height: auto; max-height: 100000000000px;" bgcolor="#fff">
                    <td style="width: 100%; padding-bottom: 40px;">
                        <table style="width: 90%; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
                            <tbody>
                            <tr style="height: auto; background-color: white; padding: 55px 0;" bgcolor="white">
                                <td style="width: 100%; padding: 15px 5px;">
                                    <a href="{{ url('/') }}" target="_blank"><img src="{{asset('front-assets/img/logo/logo.png')}}" alt="{{ env('APP_NAME') }}" style="margin: 40px auto;"></a><br>
                                    <span style="width: 100%; color: #000000; font-size: 20px;font-weight: bold;">{{ ShowLabelById(193) }}</span><br>
                                </td>
                            </tr>
                            @if($email_message && $email_message->itemByLang && $goods_item && $goods_item->itemByLang)
                                @php
                                    $parent_alias = '';
                                    if($goods_item->getSubjectId && $goods_item->getSubjectId->alias)
                                        $parent_alias = $goods_item->getSubjectId->alias
                                @endphp
                            <tr>
                                <td style="padding: 0 40px; color: #000000;font-size:  17px; line-height: 1.5; background-color: #ffffff">
                                    {!! str_replace('{goods_name}', '<a href='. url($lang, ['catalog', $parent_alias, $goods_item->alias ]) .' target="_blank">'.$goods_item->itemByLang->name.'</a>', $email_message->itemByLang->body) !!}
                                </td>
                            </tr>
                            @endif
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width: 100%; min-height: 60px; max-height: 100000000000px; padding: 5px;">
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
