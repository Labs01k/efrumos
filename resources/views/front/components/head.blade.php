@include('front.templates.after-head', ['cookie_settings' => $cookie])

@yield('meta')

<link rel="icon" type="image/png" href="{{asset('favicon.png')}}">
<link rel="apple-touch-icon" sizes="114x11" href="{{ asset('front-assets/favicon/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('front-assets/favicon/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('front-assets/favicon/favicon-16x16.png') }}">
<link rel="manifest" href="{{ asset('front-assets/favicon/site.webmanifest') }}">
<link rel="mask-icon" href="{{ asset('front-assets/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-config" content="{{ asset('front-assets/favicon/browserconfig.xml') }}">
<meta name="theme-color" content="#ffffff">

<link rel="stylesheet" href="{{  asset('front-assets/css/libs.min.css?v=').config('custom.front.css_version') }}">
<link rel="stylesheet" href="{{  asset('front-assets/css/main.css?v=').config('custom.front.css_version') }}">
<link rel="stylesheet" href="{{ asset('front-assets/css/validate.css?v=').config('custom.front.css_version') }}">
<link rel="stylesheet" href="{{ asset('front-assets/css/product-card.css?v=').config('custom.front.css_version') }}">
<link rel="stylesheet" href="{{ asset('front-assets/css/notiflix-3.2.6.min.css') }}">

<link title="Română" dir="ltr" type="text/html" rel="alternate" hreflang="ro"
      href="{{ count(request()->segments()) > 0 ? str_replace('/'.LANG, '/ro', request()->fullUrl()) : url('ro') }}">
<link title="Русский" dir="ltr" type="text/html" rel="alternate" hreflang="ru"
      href="{{ count(request()->segments()) > 0 ? str_replace('/'.LANG, '/ru', request()->fullUrl()) : url('ru') }}">

@if(count(request()->segments()) > 1)
    <link title="Русский" dir="ltr" type="text/html" rel="alternate" hreflang="ru-md"
          href="{{ count(request()->segments()) > 0 ? str_replace('/'.LANG, '/ru', request()->fullUrl()) : url('ru') }}">
    <link title="Română" dir="ltr" type="text/html" rel="alternate" hreflang="ro-md"
          href="{{ count(request()->segments()) > 0 ? str_replace('/'.LANG, '/ro', request()->fullUrl()) : url('ro') }}">
@endif

@if((count(request()->segments()) == 1 && LANG == 'ro') || count(request()->segments()) == 0)
    <link title="Română" dir="ltr" type="text/html" rel="alternate" hreflang="x-default"
          href="https://www.efrumos.md/">
    <link title="Română" dir="ltr" type="text/html" rel="alternate" hreflang="ro-md"
          href="https://www.efrumos.md/">
    <link title="Русский" dir="ltr" type="text/html" rel="alternate" hreflang="ru-md"
          href="https://www.efrumos.md/ru">
@else
    <link title="Română" dir="ltr" type="text/html" rel="alternate" hreflang="x-default"
          href="{{ count(request()->segments()) > 0 ? str_replace('/'.LANG, '/ro', request()->fullUrl()) : url('ro') }}">
@endif

@if((count(request()->segments()) == 1 && LANG == 'ro') || count(request()->segments()) == 0)
    <link rel="canonical" href="https://www.efrumos.md"/>
@elseif(count(request()->segments()) == 1 && LANG == 'ru')
    <link rel="canonical" href="https://www.efrumos.md/ru"/>
@else
    <link rel="canonical" href="{{ mb_strtolower(url()->current()) }}">
@endif

@yield('google-tag-manager')
@yield('json-ld')
