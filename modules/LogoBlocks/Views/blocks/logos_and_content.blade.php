@if( isset($heading) && !empty($heading) && isset($content) && !empty($content) && isset($logos) && !empty($logos) )
<div 
    data-block="logos-and-content"
    class="py-24 sm:py-32"
>
    <div class="container">
        <div class="grid grid-cols-1 items-center gap-x-8 gap-y-16 lg:grid-cols-2">
            <div class="mx-auto w-full max-w-xl lg:mx-0{{ isset($logos_on_left) && $logos_on_left ? ' lg:order-1' : '' }}">
                <h3 class="text-heading-3 mb-8">{{ $heading }}</h3>
                <div class="prose sm:prose-lg">{!! $content !!}</div>
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
            <div class="mx-auto grid w-full max-w-xl grid-cols-2 items-center gap-y-6 sm:gap-y-8 lg:mx-0 lg:max-w-none {{ isset($logos_on_left) && $logos_on_left ? 'lg:pr-8' : 'lg:pl-8' }}">
                @foreach( $logos as $logo )
                    @continue( empty($logo['image']) || empty($logo['image']['single']) )

                    <img
                        src="{{ $logo['image']['single']['src'] }}"
                        alt="{{ $logo['image']['single']['alt'] }}"
                        width="{{ $logo['image']['single']['width'] }}"
                        height="{{ $logo['image']['single']['height'] }}"
                        loading="lazy"
                        class="w-full max-h-24 object-contain object-left"
                    >
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif