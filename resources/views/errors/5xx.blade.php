@extends('templates.default')

@section('content')
    
    <div class="container py-4">
        <h1>Page not found</h1> 
    </div>

    @include(
        'page-builder.blocks.wysiwyg',
        [
            'heading'   => '',
            'content'   => '<p>Sorry, we can\'t find what you\'re looking for. Please use the website menus, or <a href="' . route('theme') . '">go to the home page</a>.</p>'
        ]
    )

@endsection