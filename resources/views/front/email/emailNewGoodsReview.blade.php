<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Letter</title>
</head>
<body>
<table style="max-width: 1000px; height: auto; max-height: 100000000000px; border-spacing: 0; border-collapse: collapse; font-family: Arial,sans-serif; background-color: #E47F9E; text-align: center; margin: 0 auto; padding: 0;" bgcolor="#636364">
    <tbody>
    <tr>
        <td style="width: 100%; padding: 10px 10px 0;">
            <table style="width: 100%; height: auto; max-height: 100000000000px; border-spacing: 0; border-collapse: collapse; font-family: Arial,sans-serif; margin: 0 auto;">
                <tbody>
                <tr style="background-color: #fff; height: auto; max-height: 100000000000px;" bgcolor="#fff">
                    <td style="width: 100%; padding-bottom: 40px;">
                        <table style="width: 90%; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
                            <tbody>
                            <tr style="height: auto; background-color: white;" bgcolor="white">
                                <td style="width: 100%; padding: 15px 5px 0;">
                                    <a href="{{ route('/') }}" target="_blank"><img src="{{asset('front-assets/img/logo/logo.png')}}" alt="Logo Efrumos" style="margin: 20px auto 0;"></a>
                                </td>
                            </tr>
                            @if($email_message && $email_message->itemByLang && $email_message->itemByLang->body)
                                <tr>
                                    <td style="padding: 0 40px; color: #000000;font-size:  17px; line-height: 1.5; background-color: #ffffff">
                                        {!! str_replace(['{goods_name}', '{goods_review_link}'], ['<a href=' . url(LANG, ['catalog', 'product', $goods_item_id->alias ]) . ' target="_blank" style="color:#E47F9E">'. $goods_item_id->itemByLang->name . '</a>', '<a href=' . url(LANG, ['back', 'goods-reviews']) . '?item=' . $goods_item_id->id . ' target="_blank" style="color:#E47F9E">' . ShowLabelById(271) . '</a>'], $email_message->itemByLang->body) !!}
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
