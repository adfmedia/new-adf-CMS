{{-- Note - just inner tile content here, wrapper classes to be used in parent blade (to allow for adjustable size) --}}
<div class="h-full relative isolate flex flex-col justify-end overflow-hidden rounded-2xl bg-gray-900 px-8 pb-8 pt-80 sm:pt-48 lg:pt-80">
    {{-- Image --}}
    @if( !empty($Event->image) && !empty($Event->image['listing']) )
        <img
            src="{{ $Event->image['listing']['src'] }}"
            alt="{{ $Event->image['listing']['alt'] }}"
            width="{{ $Event->image['listing']['width'] }}"
            height="{{ $Event->image['listing']['height'] }}"
            loading="lazy"
            class="absolute inset-0 -z-10 h-full w-full object-cover"
        >
    @endif
    {{-- Gradient overlay --}}
    <div class="absolute inset-0 -z-10 bg-linear-to-t from-gray-900 via-gray-900/40"></div>
    <div class="absolute inset-0 -z-10 rounded-2xl ring-1 ring-inset ring-gray-900/10"></div>
    {{-- Date & time --}}
    <div class="flex flex-wrap items-center gap-y-1 overflow-hidden text-sm text-gray-300">
        <time 
            datetime="{{ $Event->carbon_date->format("Y-m-d") }}" 
            class="w-full flex items-center"
        >
            <svg 
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="size-6 mr-1"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
            </svg>
            {{ $Event->carbon_date->format("jS F Y") }}
        </time>
        @if( $Event->start_time && $Event->start_time != '00:00:00' )
            <time
                datetime="{{ $Event->start_time }}"
                class="w-full flex items-center"
            >
                <svg 
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                    class="size-6 mr-1"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ $Event->carbon_start_time->format("g:ia") }}
                @if( $Event->end_time )
                    <span class="ml-1">- {{ $Event->carbon_end_time->format("g:ia") }}</span>
                @endif
            </time>
        @endif
    </div>
    {{-- Title --}}
    <div class="mt-3 text-white">
        <a href="{{ $Event->url }}">
            <span class="absolute inset-0"></span>{{-- Makes whole tile clickable --}}
            <div class="text-lg font-semibold leading-6 mb-2">{{ $Event->title }}</div>
            <div class="">{{ $Event->excerpt }}</div>
        </a>
    </div>
</div>