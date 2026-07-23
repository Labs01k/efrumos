@if(isset($cookie_settings) && isset($cookie_settings['before-body-end']))
    {!! $cookie_settings['before-body-end'] !!}
@endif

{!! showSettingBodyByAlias('before-body-end') !!}
