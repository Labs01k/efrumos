@if($breadcrumbs && count($breadcrumbs))
    <ul class="breadcrumbs">
        @foreach ($breadcrumbs as $breadcrumb)
            @if ($breadcrumb->url && !$loop->last)
                @php
                    $url_array = explode('/',$breadcrumb->url);
                        /*if(!$loop->first)
                          {
                            $url = url($lang).'/'.$breadcrumb->url;
                          }else{
                            $url = url($lang);
                          }*/
                @endphp
                <li>
                    <a href="{{ url(LANG, $url_array) }}">
                       {{-- @if($loop->first)
                            <svg>
                                <use xlink:href="{{ asset('front-assets/svg/sprite.svg#home') }}"></use>
                            </svg>
                        @endif--}}
                        {{ $breadcrumb->title ?? '' }}
                    </a>
                </li>
            @else
                <li>{{ $breadcrumb->title ?? '' }}</li>
            @endif
        @endforeach
    </ul>
@endif
