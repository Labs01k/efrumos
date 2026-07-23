@if($stars > 0)
    @for($i = 1; $i <= $stars; $i++)
        <img src="{{ asset('front-assets/img/icons/star-active.svg') }}" alt="Active star">
    @endfor
@endif
@if($no_stars > 0)
    @for($i = 1; $i <= $no_stars; $i++)
        <img src="{{ asset('front-assets/img/icons/star.svg') }}" alt="Star">
    @endfor
@endif
