@if ($paginator->lastPage() > 1)
    @php
        $start = $paginator->currentPage() - 4;
        $end = $paginator->currentPage() + 4;
        $last_page = $paginator->lastPage();
        if ($start < 1) $start = 1;
        if ($end >= $paginator->lastPage()) $end = $paginator->lastPage();
    @endphp

    <div class="pagination">
        @if(!empty($new_url))
            <ul>
                <li class="pagination-nav pagination-nav--prev{{ $paginator->currentPage() == 1 ? ' pagination-nav--disabled' : '' }}">
                    {!! ($paginator->currentPage() == 1) ? '<a><svg><use xlink:href="'.asset("front-assets/svg/sprite.svg#arrow-right").'"></use></svg></a>' : '<a href="'  . $new_url.'&page='.($paginator->currentPage()-1).'"><svg><use xlink:href="'.asset("front-assets/svg/sprite.svg#arrow-right").'"></use></svg></a>' !!}
                </li>
                <li {{ $paginator->currentPage() == 1 ? 'class=active' : '' }}>
                    {!! ($paginator->currentPage() == 1) ? '<a href="javascript:;">' . 1 . '</a>' : '<a href="' . $new_url . '&page=1' . '">' . 1 . '</a>' !!}
                </li>
                @if($start > 1)
                    <li>
                        <span>...</span>
                    </li>
                @endif
                @for ($i = $start + 1; $i < $end; $i++)
                    <li {{ $paginator->currentPage() == $i ? 'class=active' : '' }}>
                        {!! ($paginator->currentPage() == $i) ? '<a>' . $i . '</a>' : '<a href="' . $new_url . '&page=' . $i . '">' . $i . '</a>' !!}
                    </li>
                @endfor
                @if($end < $paginator->lastPage())
                    <li>
                        <span>...</span>
                    </li>
                @endif
                <li {{ $paginator->currentPage() == $last_page ? 'class=active' : '' }}>
                    {!! ($paginator->currentPage() == $last_page) ? '<a>' . $last_page . '</a>' : '<a href="' .  $new_url . '&page=' . $last_page . '">' . $last_page . '</a>' !!}
                </li>
                <li class="pagination-nav pagination-nav--next{{ $paginator->currentPage() == $paginator->lastPage() ? ' pagination-nav--disabled' : '' }}">
                    {!! ($paginator->currentPage() == $paginator->lastPage()) ? '<a><svg><use xlink:href="'.asset("front-assets/svg/sprite.svg#arrow-right").'"></use></svg></a>' : '<a href="' . $new_url .'&page='.($paginator->currentPage()+1). '"><svg><use xlink:href="'.asset("front-assets/svg/sprite.svg#arrow-right").'"></use></svg></a>'  !!}
                </li>
            </ul>
        @else
            <ul>
                <li class="pagination-nav pagination-nav--prev{{ $paginator->currentPage() == 1 ? ' pagination-nav--disabled' : '' }}">
                    {!! ($paginator->currentPage() == 1) ? '<a><svg><use xlink:href="'.asset("front-assets/svg/sprite.svg#arrow-right").'"></use></svg></a>' : '<a href="' . $paginator->url($paginator->currentPage()-1) . $new_url . '"><svg><use xlink:href="'.asset("front-assets/svg/sprite.svg#arrow-right").'"></use></svg></a>' !!}
                </li>
                <li {{ $paginator->currentPage() == 1 ? 'class=active' : '' }}>
                    {!! ($paginator->currentPage() == 1) ? '<a href="javascript:;">' . 1 . '</a>' : '<a href="'  . '?page=1' . '">' . 1 . '</a>' !!}
                </li>
                @if($start > 1)
                    <li>
                        <span>...</span>
                    </li>
                @endif
                @for ($i = $start + 1; $i < $end; $i++)
                    <li {{ $paginator->currentPage() == $i ? 'class=active' : '' }}>
                        {!! ($paginator->currentPage() == $i) ? '<a>' . $i . '</a>' : '<a href="' . $paginator->url($i) . $new_url . '">' . $i . '</a>' !!}
                    </li>
                @endfor
                @if($end < $paginator->lastPage())
                    <li>
                        <span>...</span>
                    </li>
                @endif
                <li {{ $paginator->currentPage() == $last_page ? 'class=active' : '' }}>
                    {!! ($paginator->currentPage() == $last_page) ? '<a>' . $last_page . '</a>' : '<a href="'  . '?page=' . $last_page . '">' . $last_page . '</a>' !!}
                </li>
                <li class="pagination-nav pagination-nav--next{{ $paginator->currentPage() == $paginator->lastPage() ? ' pagination-nav--disabled' : '' }}">
                    {!! ($paginator->currentPage() == $paginator->lastPage()) ? '<a><svg><use xlink:href="'.asset("front-assets/svg/sprite.svg#arrow-right").'"></use></svg></a>' : '<a href="' . $paginator->url($paginator->currentPage()+1) . '"><svg><use xlink:href="'.asset("front-assets/svg/sprite.svg#arrow-right").'"></use></svg></a>'  !!}
                </li>
            </ul>
        @endif
    </div>
@endif

