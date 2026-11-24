@extends('templates.default')

@section('content')
    
    <div class="container py-4 flex flex-wrap">
        <div class="w-full md:w-3/4 mb-8 md:mb-0">
            <h1 class="text-xl font-semibold mb-8">{{ $Page->h1_title }}</h1>

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

            <div class="flex flex-wrap">
                <div class="w-full md:w-1/4 md:pl-8 md:order-1">
                    <div class="bg-gray-100 p-4 space-y-4">
                        <time 
                            datetime="{{ $Page->carbon_date->format("Y-m-d") }}" 
                            class="flex items-center"
                        >
                            <svg 
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                class="size-6 mr-1"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                            </svg>
                            {{ $Page->carbon_date->format("jS F Y") }}
                        </time>
                        
                        @if( $Page->start_time && $Page->start_time != '00:00:00' )
                            <time
                                datetime="{{ $Page->start_time }}"
                                class="flex items-center"
                            >
                                <svg 
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                                    class="size-6 mr-1"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $Page->carbon_start_time->format("g:ia") }}
                                @if( $Page->end_time )
                                    <span class="ml-1">- {{ $Page->carbon_end_time->format("g:ia") }}</span>
                                @endif
                            </time>
                        @endif

                        @if( !empty($Page->address) )
                            <div class="flex">
                                <svg 
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                                    class="size-6 mr-1"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <div class="">{!! nl2br($Page->address) !!}</div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="w-full md:w-3/4">
                    <div class="prose">{!! $Page->content !!}</div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4 md:pl-8">
            @include('events::partials.sidebar')
        </div>
    </div>

@endsection