@extends('front.app')

@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">


        <div class="breadcrumbs-wrapper">
            <div class="container">
                {{ Breadcrumbs::render('news-list') }}
            </div>
        </div>

        <div class="section pt-0 news-page">
            <div class="container">
                <div class="section-head">
                    <h1 class="h2">{{ $menu_id && $menu_id->itemByLang ? $menu_id->itemByLang->name : '' }}</h1>
                </div>

                @if(!empty($news_list) && count($news_list))
                    <div class="news-page-list">
                        @foreach($news_list as $one_news_item)
                            @include('front.templates.news-item')
                        @endforeach
                    </div>
                    @include('front.templates.pagination', ['paginator' => $news_list, 'new_url' => ''])
                @else
                    <div class="basket-end-inner">
                        <div class="basket-end-icon">
                            <img src="{{ asset('front-assets/img/icons/basket-error.svg') }}" alt="Empty">
                        </div>
                        <div class="basket-end-title">{{ ShowLabelById(53) }}</div>
                        <div class="basket-end-link">
                            <a href="{{ route('/') }}">{{ ShowLabelById(54) }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

@stop
