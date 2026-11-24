@extends('templates.default')

@section('content')
    
    <div class="container py-4">
        <h1>{{ $Page->h1_title }}</h1> 
    </div>

    @if( isset($Offices) && $Offices->isNotEmpty() )
        <div class="container mb-8">
            <div
                data-map="offices-listing" data-id="{{ uniqid() }}"
                class="rounded-2xl mb-8 h-[400px] hidden"
            ></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach( $Offices as $Office )
                    <div 
                        data-office
                        data-lat="{{ $Office->latitude }}"
                        data-lng="{{ $Office->longitude }}"
                        class="h-full rounded-2xl bg-white shadow-xl"
                    >
                        @if( !empty($Office->image) && !empty($Office->image['listing']) )
                            <img
                                src="{{ $Office->image['listing']['src'] }}"
                                alt="{{ $Office->image['listing']['alt'] }}"
                                width="{{ $Office->image['listing']['width'] }}"
                                height="{{ $Office->image['listing']['height'] }}"
                                loading="lazy"
                                class="rounded-t-2xl"
                            >
                        @endif
                        <div class="p-4 space-y-3">
                            <div class="text-lg">{{ $Office->title }}</div>
                            @if( !empty($Office->email) )
                                <a 
                                    href="mailto:{{ $Office->email }}" 
                                    class="flex items-center text-primary-600 transition-colors hover:underline hover:text-primary-800"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                    <span>{{ $Office->email }}</span>
                                </a>
                            @endif
                            @if( !empty($Office->telephone) )
                                <a 
                                    href="tel:{{ $Office->telephone }}" 
                                    class="flex items-center text-primary-600 transition-colors hover:underline hover:text-primary-800"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                    <span>{{ $Office->telephone }}</span>
                                </a>
                            @endif
                            @if( !empty($Office->address) )
                                <div class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2 text-primary-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    <div>{!! nl2br($Office->address) !!}</div>
                                </div>
                            @endif
                            @if( !empty($Office->opening_times) )
                                <div class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2 text-primary-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="w-full">
                                        @foreach( $Office->opening_times as $time )
                                            @continue( empty($time['days']) || empty($time['times']) )

                                            <div class="flex items-center justify-between">
                                                <span>{{ !empty($time['days']) ? $time['days'] : '' }}</span>
                                                <span>{{ !empty($time['times']) ? $time['times'] : '' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <a href="{{ $Office->url }}" class="btn block w-full text-center">View office</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection