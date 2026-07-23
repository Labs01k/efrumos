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

<table style="width: 600px; height: auto; max-height: 100000000000px; border-spacing: 0; border-collapse: collapse; font-family: Arial,sans-serif; background-color: #E47F9E; text-align: center; margin: 0 auto; padding: 0;" bgcolor="#be2e21">
    <tbody>
    <tr>
        <td style="width: 100%; padding: 10px 10px 0;">

            <table style="width: 100%; height: auto; max-height: 100000000000px; border-spacing: 0; border-collapse: collapse; font-family: Arial,sans-serif; margin: 0 auto;">
                <tbody>

                <tr style="height: auto; background-color: #fafafa; padding: 55px 0;" bgcolor="#fafafa">
                    <td style="width: 100%; padding: 55px 5px;">
                        <img src="{{asset('front-assets/img/logo/logo.png')}}" alt="letter" style="padding-bottom: 40px; margin: 0 auto;"><br>
                        <span style="width: 100%; color:#000000; font-size: 24px;">{{ ShowLabelById(147) }}</span>
                    </td>
                </tr>
                <tr style="background-color: #fff; height: auto; max-height: 100000000000px;" bgcolor="#fff">
                    <td style="width: 100%; padding-bottom: 40px;">
                        <table style="width: 90%; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
                            <tbody>
                            @if($data['name'])
                                <tr>
                                    <td style="width: 50%; background-color: #fff; padding: 20px 10px; border: 1px solid #d3d3d3;" bgcolor="#fff"><span style="font-size: 18px; color: #101d1f;">{{ ShowLabelById(36) }}</span></td>
                                    <td style="width: 50%; background-color: #fff; padding: 20px 10px; border: 1px solid #d3d3d3;" bgcolor="#fff"><span style="font-size: 18px; color: #101d1f;">{{ $data['name'] ?? '' }}</span></td>
                                </tr>
                            @endif
                            @if($data['email'])
                                <tr>
                                    <td style="width: 50%; background-color: #fff; padding: 20px 10px; border: 1px solid #d3d3d3;" bgcolor="#fff"><span style="font-size: 18px; color: #101d1f;">{{ ShowLabelById(34) }}</span></td>
                                    <td style="width: 50%; background-color: #fff; padding: 20px 10px; border: 1px solid #d3d3d3;" bgcolor="#fff"><span style="font-size: 18px; color: #101d1f;">{{ $data['email'] ?? '' }}</span></td>
                                </tr>
                            @endif
                            @if($data['phone'])
                                <tr>
                                    <td style="width: 50%; background-color: #fff; padding: 20px 10px; border: 1px solid #d3d3d3;" bgcolor="#fff"><span style="font-size: 18px; color: #101d1f;">{{ ShowLabelById(41) }}</span></td>
                                    <td style="width: 50%; background-color: #fff; padding: 20px 10px; border: 1px solid #d3d3d3;" bgcolor="#fff"><span style="font-size: 18px; color: #101d1f;">{{ $data['phone'] ?? '' }}</span></td>
                                </tr>
                            @endif
                            @if($data['comment'])
                                <tr>
                                    <td style="width: 50%; background-color: #fff; padding: 20px 10px; border: 1px solid #d3d3d3;" bgcolor="#fff"><span style="font-size: 18px; color: #101d1f;">{{ ShowLabelById(138) }}</span></td>
                                    <td style="width: 50%; background-color: #fff; padding: 20px 10px; border: 1px solid #d3d3d3;" bgcolor="#fff"><span style="font-size: 18px; color: #101d1f;">{{ $data['comment'] ?? '' }}</span></td>
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
        <td style="width: 100%; min-height: 60px; max-height: 100000000000px; background-color: #E47F9E; padding: 5px;" bgcolor="#C4E0FD">
            <span style="color: #FFFFFF;">{{ ShowLabelById(4) }}:</span>
            <span style="color: #FFFFFF;"><a href="{{url(LANG, ['back', 'feedform', Str::slug($data['name']), 'edititem', $data['id']])}}" style="color: #FFFFFF;">{{url(LANG, ['back', 'feedform', Str::slug($data['name']), 'edititem', $data['id']])}}</a></span>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
