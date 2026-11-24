<div data-block="wysiwyg" class="container mb-8">
    @if( isset($heading) && !empty($heading) )
        <h2 class="text-3xl font-bold sm:text-4xl mb-4">{{ $heading }}</h2>
    @endif
    @if( isset($content) && !empty($content) )
        <div class="prose sm:prose-lg max-w-none">{!! $content !!}</div>
    @endif
</div>