<div class="blog-item">
    <div class="blog-img">
        <a href="{{ route('blog', $one_blog_item->alias) }}">
            <img
                src="{{ $one_blog_item->oImage && $one_blog_item->oImage->img && file_exists('upfiles/info-items/m/'. showImg($one_blog_item->oImage->img)) ? asset('upfiles/info-items/m/'. showImg($one_blog_item->oImage->img)) : asset('front-assets/img/no-image-news.png') }}"
                alt="{{ $one_blog_item->oImage->name ?? '' }}">
        </a>
    </div>
    <div class="blog-content">
        <div
            class="blog-date">{{ Carbon\Carbon::parse($one_blog_item->add_date)->locale(LANG)->isoFormat('DD MMM YYYY') }}</div>
        <h3>
            <a href="{{ route('blog', $one_blog_item->alias) }}">{{ $one_blog_item->itemByLang->name ?? '' }}</a>
        </h3>
        @if($one_blog_item->itemByLang->descr)
            <div class="blog-desc">
                <p>{{ $one_blog_item->itemByLang->descr ?? '' }}</p>
            </div>
        @endif
        <div class="link-more">
            <a href="{{ route('blog', $one_blog_item->alias) }}">
                <span>{{ ShowLabelById(51) }}</span>
                <svg>
                    <use
                        xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                </svg>
            </a>
        </div>
    </div>
</div>
