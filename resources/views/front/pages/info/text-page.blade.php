@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">

        <div class="breadcrumbs-wrapper">
            <div class="container">
                @if($parent_menu->itemByLang)
                    {{ Breadcrumbs::render($parent_menu->alias, $parent_menu) }}
                @endif
            </div>
        </div>

        <div class="section pt-0 text-page">
            <div class="container">
                <div class="text-page-inner">
                    <h1 class="h2 text-center mb-3">{{ $parent_menu->itemByLang->name ?? '' }}</h1>
                    <div class="common-text">
                        {!! $parent_menu->itemByLang->body ?? '' !!}
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop
