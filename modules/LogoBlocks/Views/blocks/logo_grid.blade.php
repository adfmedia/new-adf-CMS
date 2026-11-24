@if( isset($logos) && !empty($logos) )
<div 
    data-block="logo-grid"
    class="py-24 sm:py-32"
>
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="-mx-6 grid grid-cols-2 gap-0.5 overflow-hidden sm:mx-0 sm:rounded-2xl md:grid-cols-3">
            @foreach( $logos as $logo )
                @continue( empty($logo['image']) || empty($logo['image']['single']) )

                <div class="bg-gray-200 p-6 sm:p-10">
                    <img
                        src="{{ $logo['image']['single']['src'] }}"
                        alt="{{ $logo['image']['single']['alt'] }}"
                        width="{{ $logo['image']['single']['width'] }}"
                        height="{{ $logo['image']['single']['height'] }}"
                        loading="lazy"
                        class="w-full max-h-24 object-contain"
                    >
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif