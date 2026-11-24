<div 
    data-block="content-with-image"
    class="container flex flex-wrap items-center mb-8"
>
    <div class="w-full md:w-1/2 {{ isset($image_on_right) && $image_on_right ? 'md:pl-8 md:order-1' : 'md:pr-8' }}">
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
                    class="object-contain rounded-lg"
                >
            </picture>
        @endif
    </div>
    <div class="w-full md:w-1/2 {{ isset($image_on_right) && $image_on_right ? 'md:pr-8 md:order-none' : 'md:pl-8' }}">
        @if( isset($heading) && !empty($heading) )
            <h2 class="text-3xl font-bold sm:text-4xl mb-4">{{ $heading }}</h2>
        @endif
        @if( isset($content) && !empty($content) )
            <div class="prose sm:prose-lg">{!! $content !!}</div>
        @endif
        @if( isset($buttons) && !empty($buttons) )
            <div class="flex flex-wrap mt-8">
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