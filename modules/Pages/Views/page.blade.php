@extends('templates.default')

@section('content')
    
    <div class="container py-4">
        <h1>{{ $Page->h1_title }}</h1> 
    </div>

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection