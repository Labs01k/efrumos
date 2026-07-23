@if ($paginator->lastPage() > 1)
    @php
    $start = $paginator->currentPage() - 5;
    $end = $paginator->currentPage() + 5;
    $last_page = $paginator->lastPage();
    if ($start < 1) $start = 1;
    if ($end >= $paginator->lastPage()) $end = $paginator->lastPage();
    @endphp

    <div class="row mt-5">
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-end">
                <li class="page-item{{ ($paginator->currentPage() == 1) ? ' disabled' : '' }}">
                    {!! ($paginator->currentPage() == 1) ? '<a class="page-link" href="javascript:;" aria-label="Previous"><span aria-hidden="true">«</span></a>' : '<a href="' . $paginator->url($paginator->currentPage()-1) . '" class="page-link" aria-label="Previous"><span aria-hidden="true">«</span></a>' !!}
                </li>
                <li class="page-item{{ ($paginator->currentPage() == 1) ? ' active' : '' }}">
                    {!! ($paginator->currentPage() == 1) ? '<a class="page-link" href="javascript:;">' . 1 . '</a>' : '<a class="page-link" href="' . $paginator->url(1) . '">' . 1 . '</a>' !!}
                </li>
                @if($start > 1)
                    <li>
                        <span>...</span>
                    </li>
                @endif
                @for ($i = $start + 1; $i < $end; $i++)
                    <li class="page-item{{ ($paginator->currentPage() == $i) ? ' active' : '' }}">
                        {!! ($paginator->currentPage() == $i) ? '<a class="page-link" href="javascript:;">' . $i . '</a>' : '<a class="page-link" href="' . $paginator->url($i) . '">' . $i . '</a>' !!}
                    </li>
                @endfor
                @if($end < $paginator->lastPage())
                    <li>
                        <span>...</span>
                    </li>
                @endif
                <li class="page-item{{ ($paginator->currentPage() == $last_page) ? ' active' : '' }}">
                    {!! ($paginator->currentPage() == $last_page) ? '<a class="page-link" href="javascript:;">' . $last_page . '</a>' : '<a class="page-link" href="' . $paginator->url($last_page) . '">' . $last_page . '</a>' !!}
                </li>
                <li class="page-item{{ ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : '' }}">
                    {!! ($paginator->currentPage() == $paginator->lastPage()) ? '<a class="page-link" href="javascript:;" aria-label="Next"><span aria-hidden="true">»</span></a>' : '<a href="' . $paginator->url($paginator->currentPage()+1) . '" class="page-link" aria-label="Next"><span aria-hidden="true">»</span></a>'  !!}
                </li>
            </ul>
        </nav>
    </div>
@endif
