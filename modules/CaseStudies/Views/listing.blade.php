@extends('templates.default')

@section('content')
    
    <div class="container py-4 flex flex-wrap">
        <div class="w-full md:w-3/4 mb-8 md:mb-0">
            <h1 class="text-xl font-semibold mb-8">{{ $Page->h1_title }}</h1>
            @if( isset($Articles) && $Articles->isNotEmpty() )
                <div
                    data-load-more data-url="{{ $load_more }}"
                    class="mb-8"
                >
                    <div data-listings class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach( $Articles as $Article )
                            @include('case-studies::partials.listing-tile', ['Article' => $Article])
                        @endforeach
                    </div>
                    @if( request()->query('page', 1) == 1 && $Articles->hasMorePages() )
                        <div class="flex justify-center pt-4">
                            <a
                                data-load-more-btn
                                href="{{ $Articles->nextPageUrl() }}"
                                class="btn"
                            >Load more</a>
                        </div>
                    @else
                        {{ $Articles->links() }}
                    @endif
                </div>
            @else
                <div class="text-lg">Sorry, no case studies could be found.</div>
            @endif
        </div>
        <div class="w-full md:w-1/4 md:pl-8">
            {{-- Note > 1 - there'll always by an 'All' link, so only show others if there are any --}}
            @if( isset($categories) && sizeof($categories) > 1 )
                <h3 class="text-lg font-semibold mb-4">Categories</h3>
                <ul>
                    @foreach( $categories as $category )
                        <li class="{{ !$loop->last ? 'mb-2' : '' }}">
                            <a href="{{ $category['url'] }}">{{ $category['title'] }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

@endsection