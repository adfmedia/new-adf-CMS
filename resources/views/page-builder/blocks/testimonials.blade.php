@if( isset($testimonials) && $testimonials instanceof \Illuminate\Database\Eloquent\Collection && $testimonials->isNotEmpty() )
<div
    data-block="testimonials"
    class="container mb-8"
>
    @if( isset($heading) && !empty($heading) )
        <h3 class="text-2xl font-bold sm:text-3xl mb-4 px-12">{{ $heading }}</h3>
    @endif
    @if( isset($content) && !empty($content) )
        <div class="px-12 prose sm:prose-lg">{!! $content !!}</div>
    @endif
    <div data-carousel data-config="testimonials" class="swiper testimonials-carousel">
        <div class="swiper-wrapper">
            @foreach( $testimonials as $Testimonial )
                <div class="swiper-slide px-12{{ !$loop->first ? ' swiper-hidden' : '' }}">
                    <div class="h-full relative overflow-hidden bg-gray-900 px-6 py-20 shadow-xl sm:rounded-3xl sm:px-10 sm:py-24 md:px-12 lg:px-20">
                        @if( !empty($Testimonial->image) && !empty($Testimonial->image['desktop']) )
                            <picture>
                                @if( !empty($Testimonial->image['mobile']) )
                                    <source
                                        media="(max-width: 640px)"
                                        srcset="{{ $Testimonial->image['mobile']['src'] }}"
                                    >
                                @endif
                                @if( !empty($Testimonial->image['tablet']) )
                                    <source
                                        media="(min-width: 641px) and (max-width: 1024px)"
                                        srcset="{{ $Testimonial->image['tablet']['src'] }}"
                                    >
                                @endif
                                <source
                                    media="(min-width: 1025px)"
                                    srcset="{{ $Testimonial->image['desktop']['src'] }}"
                                >
                                <img
                                    src="{{ $Testimonial->image['desktop']['src'] }}"
                                    alt="{{ $Testimonial->image['desktop']['alt'] }}"
                                    width="{{ $Testimonial->image['desktop']['width'] }}"
                                    height="{{ $Testimonial->image['desktop']['height'] }}"
                                    loading="lazy"
                                    class="absolute inset-0 h-full w-full object-cover brightness-150 saturate-0"
                                >
                            </picture>
                            <div class="absolute inset-0 bg-gray-900/90 mix-blend-multiply"></div>
                            <div class="absolute -left-80 -top-56 transform-gpu blur-3xl" aria-hidden="true">
                                <div class="aspect-[1097/845] w-[68.5625rem] bg-linear-to-r from-[#ff4694] to-[#776fff] opacity-[0.45]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
                            </div>
                            <div class="hidden md:absolute md:bottom-16 md:left-[50rem] md:block md:transform-gpu md:blur-3xl" aria-hidden="true">
                                <div class="aspect-[1097/845] w-[68.5625rem] bg-linear-to-r from-[#ff4694] to-[#776fff] opacity-25" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
                            </div>
                        @endif
                        <div class="relative mx-auto max-w-2xl lg:mx-0">
                            <figure>
                                <blockquote class="mt-6 prose sm:prose-lg font-semibold text-white">{!! $Testimonial->quote !!}</blockquote>
                                <figcaption class="mt-6 text-base text-white">
                                    <div class="font-semibold">{{ $Testimonial->citation }}</div>
                                    @if( !empty($Testimonial->job_title) )
                                        <div class="mt-1">{{ $Testimonial->job_title }}</div>
                                    @endif
                                </figcaption>
                            </figure>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="swiper-pagination"></div>

        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</div>
@endif