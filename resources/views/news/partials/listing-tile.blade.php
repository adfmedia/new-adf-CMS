{{-- Note - just inner tile content here, wrapper classes to be used in parent blade (to allow for adjustable size) --}}
<div class="h-full relative isolate flex flex-col justify-end overflow-hidden rounded-2xl bg-gray-900 px-8 pb-8 pt-80 sm:pt-48 lg:pt-80">
    {{-- Image --}}
    @if( !empty($Article->image) && !empty($Article->image['listing']) )
        <img
            src="{{ $Article->image['listing']['src'] }}"
            alt="{{ $Article->image['listing']['alt'] }}"
            width="{{ $Article->image['listing']['width'] }}"
            height="{{ $Article->image['listing']['height'] }}"
            loading="lazy"
            class="absolute inset-0 -z-10 h-full w-full object-cover"
        >
    @endif
    {{-- Gradient overlay --}}
    <div class="absolute inset-0 -z-10 bg-linear-to-t from-gray-900 via-gray-900/40"></div>
    <div class="absolute inset-0 -z-10 rounded-2xl ring-1 ring-inset ring-gray-900/10"></div>
    {{-- Date & author --}}
    <div class="flex flex-wrap items-center gap-y-1 overflow-hidden text-sm leading-6 text-gray-300">
        <time datetime="{{ $Article->article_date_carbon->format("Y-m-d") }}" class="mr-8">{{ $Article->article_date_carbon->format("jS M Y") }}</time>
        @if( $Article->author )
            <div class="-ml-4 flex items-center gap-x-4">
                <svg viewBox="0 0 2 2" class="-ml-0.5 size-0.5 flex-none fill-white/50">
                    <circle cx="1" cy="1" r="1" />
                </svg>
                <div class="flex gap-x-2.5">
                    @if( !empty($Article->author->image) && !empty($Article->author->image['listing']) )
                        <img 
                            src="{{ $Article->author->image['listing']['src'] }}" 
                            alt="{{ $Article->author->image['listing']['alt'] }}" 
                            width="{{ $Article->author->image['listing']['width'] }}" 
                            height="{{ $Article->author->image['listing']['height'] }}" 
                            loading="lazy"
                            class="size-6 flex-none rounded-full bg-white/10"
                        >
                    @endif
                    {{ $Article->author->title }}
                </div>
            </div>
        @endif
    </div>
    {{-- Title --}}
    <div class="mt-3 text-white">
        <a href="{{ $Article->url }}">
            <span class="absolute inset-0"></span>{{-- Makes whole tile clickable --}}
            <div class="text-lg font-semibold leading-6 mb-2">{{ $Article->title }}</div>
            <div class="">{{ $Article->excerpt }}</div>
        </a>
    </div>
</div>