<div class="news-item">
    <div class="news-img">
        <a href="{{ route('news', $one_news_item->alias) }}">
            <img
                src="{{ $one_news_item->oImage && $one_news_item->oImage->img && file_exists('upfiles/info-items/m/'. showImg($one_news_item->oImage->img)) ? asset('upfiles/info-items/m/'. showImg($one_news_item->oImage->img)) : asset('front-assets/img/no-image-news.png') }}"
                alt="{{ $one_news->itemByLang->name ?? '' }}">
        </a>
    </div>
    <div class="news-content">
        <div
            class="news-date">{{ Carbon\Carbon::parse($one_news_item->add_date)->locale(LANG)->isoFormat('DD MMM YYYY') }}</div>
        <h3>
            <a href="{{ route('news', $one_news_item->alias) }}">{{ $one_news_item->itemByLang->name ?? '' }}</a>
        </h3>
        <div class="link-more">
            <a href="{{ route('news', $one_news_item->alias) }}">
                <span>{{ ShowLabelById(262) }}</span>
                <svg>
                    <use
                        xlink:href="{{ asset('front-assets/svg/sprite.svg#arrow-right') }}"></use>
                </svg>
            </a>
        </div>
    </div>
</div>
