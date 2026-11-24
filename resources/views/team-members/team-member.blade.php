@extends('templates.default')

@section('content')
    
    <div class="container py-4">
        @if( !empty($Page->image) && !empty($Page->image['desktop']) )
            <picture>
                @if( !empty($Page->image['mobile']) )
                    <source
                        media="(max-width: 640px)"
                        srcset="{{ $Page->image['mobile']['src'] }}"
                    >
                @endif
                <source
                    media="(min-width: 641px)"
                    srcset="{{ $Page->image['desktop']['src'] }}"
                >
                <img
                    src="{{ $Page->image['desktop']['src'] }}"
                    alt="{{ $Page->image['desktop']['alt'] }}"
                    width="{{ $Page->image['desktop']['width'] }}"
                    height="{{ $Page->image['desktop']['height'] }}"
                    class="mb-8 rounded-md"
                >
            </picture>
        @endif

        <h1 class="text-heading-1 mb-1">{{ $Page->title }}</h1>
        @if( !empty($Page->job_title) )
            <h3 class="text-heading-3">{{ $Page->job_title }}</h3>
        @endif

        <div class="flex flex-wrap mt-8">
            <div class="w-full md:w-1/2 md:pr-4 mb-8">
                <div class="prose sm:prose-lg">{!! $Page->content !!}</div>
            </div>
            @if( !empty($Page->email) || !empty($Page->telephone) || !empty($Page->social_links) )
                <div class="w-full md:w-1/2 md:pl-4 space-y-3 mb-8">
                    <h4 class="text-heading-4 mb-4">Contact details</h4>
                    @if( !empty($Page->email) )
                        <a 
                            href="mailto:{{ $Page->email }}" 
                            class="flex items-center text-primary-600 transition-colors hover:underline hover:text-primary-800"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            <span>{{ $Page->email }}</span>
                        </a>
                    @endif
                    @if( !empty($Page->telephone) )
                        <a 
                            href="tel:{{ $Page->telephone }}" 
                            class="flex items-center text-primary-600 transition-colors hover:underline hover:text-primary-800"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                            <span>{{ $Page->telephone }}</span>
                        </a>
                    @endif
                    @if( !empty($Page->social_links) )
                        <ul role="list" class="mt-6 flex gap-x-6 relative">
                            @foreach( $Page->social_links as $link )
                                @continue( empty($link['platform']) || empty($link['link']))

                                <li>
                                    <a href="{{ $link['link'] }}" class="text-gray-400 hover:text-gray-500">
                                        @switch( $link['platform'] )
                                            @case('linkedin')
                                                <span class="sr-only">LinkedIn</span>
                                                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd" />
                                                </svg>
                                                @break
                                            @case('x')
                                                <span class="sr-only">X / Twitter</span>
                                                <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/>
                                                </svg>
                                                @break
                                            @case('facebook')
                                                <span class="sr-only">Facebook</span>
                                                <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                </svg>
                                                @break
                                        @endswitch
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection