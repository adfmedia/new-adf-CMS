@if( isset($articles) && $articles instanceof \Illuminate\Database\Eloquent\Collection && $articles->isNotEmpty() )
<div 
    data-block="news-listing"
    class="container mb-8"
>
    @if( isset($heading) && !empty($heading) )
        <h2 class="text-3xl font-bold sm:text-4xl mb-4">{{ $heading }}</h2>
    @endif
    @if( isset($content) && !empty($content) )
        <div class="prose sm:prose-lg mb-4">{!! $content !!}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach( $articles as $Article )
            @include('news.partials.listing-tile', ['Article' => $Article])
        @endforeach
    </div>
</div>
@endif