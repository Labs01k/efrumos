<!doctype html>
<html lang="{{LANG}}">
<head>
    <x-head :cookie="$cookie_settings"/>

    {{-- for add styles --}}
    @yield('styles')
</head>
<body {!! $attributes ?? '' !!}>
    <x-header :cookie="$cookie_settings"/>
<div class="page-wrapper">

    @yield('container')

    <x-footer :cookie="$cookie_settings"/>

    {{-- for add scripts --}}
    @stack('other-scripts')

</div>
</body>
</html>

