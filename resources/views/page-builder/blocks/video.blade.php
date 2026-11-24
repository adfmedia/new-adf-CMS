@if( isset($video) && !empty($video) )
<div
    data-block="video"
    class="container mb-8"
>
    <a
        data-lightbox data-group="{{ uniqid() }}"
        href="{{ $video }}"
        class="block rounded-lg relative isolate overflow-hidden bg-gray-900 py-24 sm:py-32 group"
    >
        @if( isset($image) && !empty($image) && !empty($image['desktop']) )
            <picture>
                @if( !empty($image['mobile']) )
                    <source
                        media="(max-width: 640px)"
                        srcset="{{ $image['mobile']['src'] }}"
                    >
                @endif
                <source
                    media="(min-width: 641px)"
                    srcset="{{ $image['desktop']['src'] }}"
                >
                <img
                    src="{{ $image['desktop']['src'] }}"
                    alt="{{ $image['desktop']['alt'] }}"
                    width="{{ $image['desktop']['width'] }}"
                    height="{{ $image['desktop']['height'] }}"
                    loading="lazy"
                    class="absolute inset-0 -z-10 h-full w-full object-cover"
                >
            </picture>

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-gray-900/40"></div>
        @endif
        <div class="relative flex flex-col items-center justify-center space-y-8">
            @if( isset($heading) && !empty($heading) )
                <h2 class="text-4xl font-bold text-white sm:text-6xl">{{ $heading }}</h2>
            @endif
            @if( isset($content) && !empty($content) )
                <div class="prose sm:prose-lg text-white">{!! $content !!}</div>
            @endif
            <svg class="text-white size-24 transition-colors group-hover:text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z" />
            </svg>
        </div>
    </a>
</div>
@endif