<div 
    data-block="banner"
    class="relative isolate overflow-hidden bg-gray-900 py-24 sm:py-32 mb-8"
>
    @if( isset($image) && !empty($image) && !empty($image['desktop']) )
        @pushIf($BlockObj->should_preload, 'preloads')
            <link rel="preload" href="{{ $image['desktop']['src'] }}" as="image" fetchpriority="high" media="(min-width: 641px)">
            @if( !empty($image['mobile']) )
                <link rel="preload" href="{{ $image['mobile']['src'] }}" as="image" fetchpriority="high" media="(max-width: 640px)">
            @endif
        @endPushIf
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
                class="absolute inset-0 -z-10 h-full w-full object-cover"
            >
        </picture>

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-gray-900/40"></div>
    @endif
    <div class="container relative flex flex-col space-y-8">
        @if( isset($heading) && !empty($heading) )
            <h2 class="text-4xl font-bold text-white sm:text-6xl">{{ $heading }}</h2>
        @endif
        @if( isset($content) && !empty($content) )
            <div class="prose sm:prose-lg text-white">{!! $content !!}</div>
        @endif
        @if( isset($buttons) && !empty($buttons) )
            <div class="flex flex-wrap">
                @foreach( $buttons as $button )
                    @continue(empty($button['text']) || empty($button['link']) || empty($button['link']['url']) )

                    <a
                        href="{{ $button['link']['url'] }}"{!! $button['link']['target'] !!}
                        class="btn{{ !empty($button['colour']) ? ' ' . $button['colour'] : '' }} mr-4"
                    >{{ $button['text'] }}</a>
                @endforeach
            </div>
        @endif
    </div>
</div>