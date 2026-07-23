@if(isset($cookie_settings) && isset($cookie_settings['after-head']))
    {!! $cookie_settings['after-head'] !!}
@endif

@if(isset($cookie_settings) && isset($cookie_settings['for-advertisement']))
    {!! $cookie_settings['for-advertisement'] !!}
@endif
