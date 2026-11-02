<!DOCTYPE html>
{{--
    EFA-43 / E1-BES-05 — страница открывается приложением внутри WebView,
    не часть обычного сайта (как payment/victoriabank-redirect.blade.php) —
    своя минимальная вёрстка, без шапки/меню сайта.
--}}
<html lang="{{ $lang ?? app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trans('variables.account_link_title') }}</title>
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
        }
        .link-box { width: 100%; max-width: 360px; padding: 24px; box-sizing: border-box; }
        .link-box h1 { font-size: 18px; margin: 0 0 16px; text-align: center; }
        .link-box input {
            width: 100%; box-sizing: border-box; padding: 12px; margin-bottom: 10px;
            border: 1px solid #ddd; border-radius: 6px; font-size: 16px;
        }
        .link-box button, .link-box a.social-btn {
            display: block; width: 100%; box-sizing: border-box; padding: 12px;
            margin-bottom: 10px; border: none; border-radius: 6px; font-size: 16px;
            text-align: center; text-decoration: none; cursor: pointer;
        }
        .link-box button[type="submit"] { background: #db6e97; color: #fff; }
        .link-box a.social-btn { background: #fff; border: 1px solid #ddd; color: #465061; }
        .link-box .error { color: #c0392b; font-size: 14px; margin: -4px 0 10px; display: none; }
        .link-box .divider { text-align: center; color: #999; font-size: 13px; margin: 14px 0; }
    </style>
</head>
<body>
<div class="link-box">
    <h1>{{ trans('variables.account_link_heading') }}</h1>

    <form id="accountLinkForm">
        <input type="email" name="email" placeholder="{{ trans('variables.account_link_email_placeholder') }}" required>
        <input type="password" name="password" placeholder="{{ trans('variables.account_link_password_placeholder') }}" required>
        <div class="error" id="accountLinkError"></div>
        <button type="submit">{{ trans('variables.account_link_submit') }}</button>
    </form>

    <div class="divider">{{ trans('variables.account_link_or') }}</div>

    <a class="social-btn" href="{{ route('login-google') }}">{{ trans('variables.account_link_google') }}</a>
    <a class="social-btn" href="{{ route('login-facebook') }}">{{ trans('variables.account_link_facebook') }}</a>
</div>
<script>
    document.getElementById('accountLinkForm').addEventListener('submit', function (e) {
        e.preventDefault();

        var form = e.target;
        var errorBox = document.getElementById('accountLinkError');
        errorBox.style.display = 'none';

        fetch('{{ route('ajax-login-user') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: new URLSearchParams(new FormData(form)),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.status && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                errorBox.textContent = data.message || '{{ trans('variables.account_link_error') }}';
                errorBox.style.display = 'block';
            })
            .catch(function () {
                errorBox.textContent = '{{ trans('variables.account_link_error') }}';
                errorBox.style.display = 'block';
            });
    });
</script>
</body>
</html>
