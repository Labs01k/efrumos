@php
    $no_image = '';
    switch ($columns_count) {
     case 2:
         $img_size = 'm';
         $button_color = ' button--white';
         $no_image = 'no-image-banners.png';
         break;
     case 3:
         if(isset($square_banner) && $square_banner == 1){
            $img_size = 'l';
            $no_image = 'no-image-l.png';
         }else{
            $img_size = 's';
            $no_image = 'no-image-banners.png';
         }

         $button_color = '';
         break;
     default:
         break;
 }
@endphp

<div class="banners-item">
    <div class="banners-img">
        <img
            src="{{ $one_item->oImage && $one_item->oImage->img && file_exists('upfiles/banners/'.$img_size.'/'. showImg($one_item->oImage->img)) ? asset('upfiles/banners/'.$img_size.'/'. showImg($one_item->oImage->img)) : asset('front-assets/img/'.$no_image) }}"
            alt="{{ $one_item->oImage->name ?? '' }}">
    </div>
    @if($one_item->itemByLang->link)
        <a href="{{ $one_item->itemByLang->link ?? '' }}" class="banners-content">
            <div class="banners-text">
                @if($one_item->itemByLang->link_name)
                    <h2{{ $one_item->color_code ? ' style=color:'.$one_item->color_code : '' }}>{{ $one_item->itemByLang->name ?? '' }}</h2>
                @endif
                @if($one_item->itemByLang->short_descr)
                    <p{{ $one_item->color_code ? ' style=color:'.$one_item->color_code : '' }}>{{ $one_item->itemByLang->short_descr ?? '' }}</p>
                @endif
            </div>
            @if($one_item->itemByLang->link_name)
                <div class="banners-link">
                    <span
                        class="button{{ $button_color ?? '' }}" {{ $one_item->color_code_button ? 'style=color:'.$one_item->color_code_button : '' }}>{{ $one_item->itemByLang->link_name ?? '' }}</span>
                </div>
            @endif
        </a>
    @else
        <div class="banners-content">
            <div class="banners-text">
                @if($one_item->itemByLang->link_name)
                    <h2{{ $one_item->color_code ? ' style=color:'.$one_item->color_code : '' }}>{{ $one_item->itemByLang->name ?? '' }}</h2>
                @endif
                @if($one_item->itemByLang->short_descr)
                    <p{{ $one_item->color_code ? ' style=color:'.$one_item->color_code : '' }}>{{ $one_item->itemByLang->short_descr ?? '' }}</p>
                @endif
            </div>
            @if($one_item->itemByLang->link_name)
                <div class="banners-link">
                    <span
                        class="button{{ $button_color ?? '' }}" {{ $one_item->color_code_button ? 'style=color:'.$one_item->color_code_button : '' }}>{{ $one_item->itemByLang->link_name ?? '' }}</span>
                </div>
            @endif
        </div>
    @endif
</div>
