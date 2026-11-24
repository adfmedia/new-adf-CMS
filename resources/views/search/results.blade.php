@extends('templates.default')

@section('content')

    <div class="container pt-4 mb-8">
        <form action="" method="GET" class="mx-auto max-w-7xl mb-8 text-center">
            <h1 class="text-2xl font-bold leading-10 tracking-tight mb-6">{{ $Page->h1_title }}</h1>
            <div class="flex justify-center text-left">
                <div class="relative border border-gray-400 rounded-md p-1 w-full max-w-lg">
                    <input 
                        type="search" name="search" value="{{ $search_term }}" 
                        class="w-full border-0 pr-8" 
                    >
                    <button type="submit" class="absolute top-0 right-0 pr-1 w-6 h-full flex items-center">
                        <span class="sr-only">Search</span>
                        <svg class="size-4 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>

        @if( $Results )
            <div class="space-y-8 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:gap-y-8 sm:space-y-0 lg:gap-x-10">
                @foreach( $Results as $Result )
                    <a
                        href="{{ $Result->url }}"
                        class="bg-white p-4 shadow-lg rounded-lg group transition-colors hover:bg-gray-100"
                    >
                        <div class="text-base font-semibold leading-7 text-gray-900 transition-colors group-hover:text-primary-600">{{ $Result->title }}</div>
                        <div class="mt-2 text-base leading-7 text-gray-600">{!! $Result->search_snippet !!}</div>
                    </a>
                @endforeach
            </div>
            {{ $Results->links() }}
        @endif

    </div>

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection