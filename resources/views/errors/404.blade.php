@php
    if (!isset($Page)) {
        $Page = \App\Models\Url::getDisabledSystemPage('page', '404');
    }
@endphp
@extends('templates.default')

@section('content')
    
    <div class="container py-4">
        <h1>{{ $Page ? $Page->h1_title : 'Page not found' }}</h1> 
    </div>

    @if( $Page )
        @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])
    @else
        @include(
            'page-builder.blocks.wysiwyg',
            [
                'heading'   => '',
                'content'   => '<p>Sorry, we can\'t find what you\'re looking for. Please use the website menus, or <a href="' . route('theme') . '">go to the home page</a>.</p>'
            ]
        )
    @endif

@endsection