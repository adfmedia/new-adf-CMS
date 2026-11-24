@if( isset($tiles) && !empty($tiles) )
<div data-block="cta-tiles" class="container mb-8">
    <div class="flex flex-wrap items-stretch -mx-4">
        @foreach( $tiles as $tile )
            <div class="w-full sm:w-1/2 lg:w-1/3 px-4 mb-4">
                <div class="flex flex-col justify-end overflow-hidden rounded-2xl bg-white shadow-xl">
                    @if( !empty($tile['image']) && !empty($tile['image']['single']) )
                        <img
                            src="{{ $tile['image']['single']['src'] }}"
                            alt="{{ $tile['image']['single']['alt'] }}"
                            width="{{ $tile['image']['single']['width'] }}"
                            height="{{ $tile['image']['single']['height'] }}"
                            loading="lazy"
                            class=""
                        >
                    @endif
                    <div class="flex-1 flex flex-col justify-between p-4">
                        @if( !empty($tile['heading']) )
                            <div class="text-lg sm:text-xl font-semibold mb-4">{{ $tile['heading'] }}</div>
                        @endif
                        @if( !empty($tile['cta']) && !empty($tile['cta']['text']) && !empty($tile['cta']['link']) && !empty($tile['cta']['link']['url']) )
                            <a
                                href="{{ $tile['cta']['link']['url'] }}"{!! $tile['cta']['link']['target'] !!}
                                class="btn w-full text-center"
                            >{{ $tile['cta']['text'] }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif