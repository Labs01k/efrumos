<!DOCTYPE html>
{{--
    Промежуточная страница перед уходом на защищённую страницу банка.
    Покупатель видит её долю секунды, но текст всё равно должен быть на его
    языке; имя банка не называем — адреса платёжных роутов тоже обезличены.
--}}
<html lang="{{ $lang ?? app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ trans('variables.payment_redirect_title') }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafafa;
            color: #465061;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 16px;
            text-align: center;
        }
        .pay-redirect { padding: 24px; }
        .pay-redirect p { margin: 0; }
        .pay-redirect-spinner {
            width: 32px;
            height: 32px;
            margin: 0 auto 16px;
            border: 3px solid #eeeeee;
            border-top-color: #db6e97;
            border-radius: 50%;
            animation: pay-spin 900ms linear infinite;
        }
        @keyframes pay-spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            .pay-redirect-spinner { animation: none; }
        }
    </style>
</head>
<body>
<div class="pay-redirect">
    <div class="pay-redirect-spinner" aria-hidden="true"></div>
    <p>{{ trans('variables.payment_redirect_text') }}</p>
</div>
<form id="vbForm" method="POST" action="{{ $endpoint }}">
    @foreach ($fields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <noscript>
        <button type="submit">{{ trans('variables.payment_redirect_button') }}</button>
    </noscript>
</form>
<script>document.getElementById('vbForm').submit();</script>
</body>
</html>
