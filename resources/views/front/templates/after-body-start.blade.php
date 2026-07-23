@if(isset($cookie_settings) && isset($cookie_settings['after-body-start']))
    {!! $cookie_settings['after-body-start'] !!}
@endif
{!! showSettingBodyByAlias('after-body-start') !!}
