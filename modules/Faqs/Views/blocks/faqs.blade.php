@if( isset($faq_category) && $faq_category )
<div
    data-block="faqs"
    class="container mb-8"
>
    @if( isset($heading) && !empty($heading) )
        <h2 class="text-3xl font-bold sm:text-4xl mb-4">{{ $heading }}</h2>
    @endif
    @if( isset($content) && !empty($content) )
        <div class="prose sm:prose-lg mb-4">{!! $content !!}</div>
    @endif

    <div data-accordion-wrap class="accordion-wrap">
        @foreach( $faq_category->faqs as $Faq )
            <div data-accordion-item class="accordion-item{{ $loop->first ? ' open' : '' }}">
                <a data-item-toggle href="#" class="item-heading">
                    <div class="item-title">{{ $Faq->question }}</div>
                    <svg class="toggle-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </a>
                <div class="item-content">
                    <div class="prose sm:prose-lg">{!! $Faq->answer !!}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif