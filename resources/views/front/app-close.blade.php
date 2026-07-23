<!DOCTYPE html>
<html lang="{{LANG}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{env('APP_NAME')}}</title>
    <link rel="icon" type="image/png" href="{{asset('favicon.png')}}">
    <link rel="apple-touch-icon-precomposed" href="{{asset('favicon.png')}}">

    <style>
        html {
            height: 100%;
            padding: 0;
            margin: 0;
        }
        body {
            text-align: center;
            height: 100%;
            padding: 0;
            margin: 0;
            background: #f0f0f0;
        }
        img {
            position: absolute;
            top: 50%;
            left: 50%;
            width: auto;
            height: auto;
            padding: 0;
            margin: 0;
            transform: translate(-50%, -50%);
        }
    </style>
</head>
<body>

<img src="{{asset('front-assets/img/under-construction.png')}}" alt="under-construction" title="Under Construction">

</body>
</html>
