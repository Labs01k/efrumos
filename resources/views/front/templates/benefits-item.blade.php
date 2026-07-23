<div class="benefits-item">
    <div class="benefits-icon">
        <img
            src="{{ $one_advantage->oImage && $one_advantage->oImage->img && file_exists('upfiles/menu/'. showImg($one_advantage->oImage->img)) ? asset('upfiles/menu/'. showImg($one_advantage->oImage->img)) : asset('front-assets/img/no-image-wb-xs.png') }}"
            alt="{{ $one_advantage->oImage->name ?? '' }}">
    </div>
    <div class="benefits-text">
        <h3>{{ $one_advantage->itemByLang->name ?? '' }}</h3>
        <p>{{ $one_advantage->itemByLang->short_descr ?? '' }}</p>
    </div>
</div>
