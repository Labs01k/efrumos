@extends('front.app')
@section('meta')
    <x-meta :meta="$meta"/>
@stop

@section('container')

    <div class="page-content">
        <div class="not-found">
            <div class="container">
                <div class="not-found-img">
                    <img src="{{ asset('front-assets/img/not-found.png') }}" alt="404">
                </div>
                <div class="not-found-title">{{ ShowLabelById(274) }}</div>
                <div class="not-found-text">
                    <p>{{ ShowLabelById(275) }}</p>
                </div>
                <div class="not-found-link">
                    <a href="{{ route('/') }}">{{ ShowLabelById(276) }}</a>
                </div>
            </div>
        </div>
    </div>

@stop
