<!DOCTYPE html>
<html lang="{{LANG}}">
<head>
    @stack('after-head-scripts')

    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta charset="UTF-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    @if(!empty($modules_submenu_name))
        <title>{{$modules_submenu_name->name ?? trans('variables.title_page')}}</title>
    @elseif(!empty($modules_name))
        <title>{{$modules_name->name ?? trans('variables.title_page')}}</title>
    @else
        <title>{{trans('variables.title_page')}}</title>
    @endif

    <link rel="shortcut icon" href="{{asset('favicon.ico')}}">
    <link rel="icon" type="image/png" href="{{asset('favicon.png')}}">
    <link rel="apple-touch-icon-precomposed" href="{{asset('favicon.png')}}">

    <link rel="stylesheet" href="{{asset('admin-assets/css/plugins/simplebar.css')}}">
    <link rel="stylesheet" href="{{asset('admin-assets/css/plugins/metisMenu.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin-assets/css/plugins/dropzone.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin-assets/css/plugins/fancybox.css')}}">
    <link rel="stylesheet" href="{{asset('admin-assets/css/plugins/flatpickr.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin-assets/css/plugins/notiflix-3.2.5.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin-assets/css/plugins/select2.min.css')}}">
    {{--<link rel="stylesheet" href="{{asset('admin-assets/css/plugins/select2-bootstrap4.css')}}">--}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('admin-assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin-assets/css/app.css')}}">
    <link rel="stylesheet" href="{{asset('admin-assets/css/icons.css')}}">
    <link rel="stylesheet" href="{{asset('admin-assets/css/custom.css?v=').config('custom.back.css_version')}}">
</head>
<body {{ request()->segment(4) == 'login' ? 'class=bg-login' : '' }}>
@stack('after-body-start-scripts')

<div class="wrapper">
    @auth
        @include('admin.header')
    @endauth

    @auth
        @include('admin.templates.sidebar')
    @endauth

    @yield('content')

    @auth
        @include('admin.footer')
    @endauth
</div>

<script src="{{asset('admin-assets/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('admin-assets/js/jquery.min.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/simplebar.min.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/metisMenu.min.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/select2.min.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/jquery-ui.min.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/dropzone.min.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/dropzone-config.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/fancybox.umd.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/flatpickr.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/notiflix-3.2.5.min.js')}}"></script>
<script src="{{asset('admin-assets/js/app.js')}}"></script>
<script src="{{asset('admin-assets/js/plugins/table-responsive-scrollbar-top.js')}}"></script>

<script src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_MAP_API')}}"></script>
<script src="{{asset('admin-assets/js/google_map.js')}}"></script>
<script src="{{asset('admin-assets/js/custom.js?v=').config('custom.back.js_version')}}"></script>
<script src="{{asset('admin-assets/js/ckeditor5/build/ckeditor.js')}}"></script>
<script src="{{asset('admin-assets/js/ckeditor5/ckfinder/ckfinder.js')}}"></script>
<script src="{{asset('admin-assets/js/ckeditor5/build/translations/'.LANG.'.js')}}"></script>
@stack('other-scripts')
</body>
</html>
