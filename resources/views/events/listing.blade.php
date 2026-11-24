@extends('templates.default')

@section('content')
    
    <div class="container py-4 flex flex-wrap">
        <div class="w-full md:w-3/4 mb-8 md:mb-0">
            <h1 class="text-xl font-semibold mb-8">{{ $Page->h1_title }}</h1>
            @if( isset($Events) && $Events->isNotEmpty() )
                <div class="mb-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                        @foreach( $Events as $Event )
                            @include('events.partials.listing-tile')
                        @endforeach
                    </div>
                    {{ $Events->links() }}
                </div>
            @else
                <div class="text-lg">Sorry, no events could be found.</div>
            @endif
        </div>
        <div class="w-full md:w-1/4 md:pl-8">
            @include('events.partials.sidebar')
        </div>
    </div>

@endsection