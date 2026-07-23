<li>
    <a href="{{ $one_item->link ?? '' }}" target="_blank">
        @if($icon_type == 'svg' && $one_item->icon_name)
            <svg>
                <use
                    xlink:href="{{ asset('front-assets/svg/sprite.svg#'. $one_item->icon_name) }}"></use>
            </svg>
        @elseif($icon_type == 'img' && $one_item->img)
            <img
                src="{{ $one_item->img && file_exists('upfiles/social-media/' . $one_item->img) ? asset('upfiles/social-media/'.$one_item->img) : asset('front-assets/img/no-image-xs.png') }}"
                alt="{{ $one_item->itemByLang->name ?? '' }}">
        @else
            <img
                src="{{ asset('front-assets/img/no-image-xs.png') }}"
                alt="No image">
        @endif
    </a>
</li>
