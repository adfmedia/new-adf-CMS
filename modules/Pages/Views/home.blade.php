@extends('templates.default')

@section('content')

    @if( $Page->page_builder && $Page->page_builder->isNotEmpty() )
        @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])
    @else
        {{-- A home page with no blocks yet, e.g. straight after install --}}
        <div class="container py-16 lg:py-24">
            <h1 class="text-heading-3 lg:text-heading-1 font-bold text-primary mb-4">{{ $Page->h1_title ?: config('app.name') }}</h1>
            <p class="text-lg text-gray-600 max-w-2xl">This is the starter home page. Sign in to the CMS and edit the Home page to build it with blocks.</p>
            <span class="block mt-8 h-1 w-16 bg-secondary"></span>
        </div>
    @endif

@endsection

@push('schema')
@include('includes.schema.site-search')
@endpush