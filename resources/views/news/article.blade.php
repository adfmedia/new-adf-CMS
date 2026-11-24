@extends('templates.default')

@section('content')
    
    <div class="container py-4 flex flex-wrap">
        <div class="w-full md:w-3/4 mb-8 md:mb-0">
            <h1 class="text-xl font-semibold mb-8">{{ $Page->h1_title }}</h1>

            @if( !empty($Page->image) && !empty($Page->image['desktop']) )
                <picture>
                    @if( !empty($Page->image['mobile']) )
                        <source
                            media="(max-width: 640px)"
                            srcset="{{ $Page->image['mobile']['src'] }}"
                        >
                    @endif
                    <source
                        media="(min-width: 641px)"
                        srcset="{{ $Page->image['desktop']['src'] }}"
                    >
                    <img
                        src="{{ $Page->image['desktop']['src'] }}"
                        alt="{{ $Page->image['desktop']['alt'] }}"
                        width="{{ $Page->image['desktop']['width'] }}"
                        height="{{ $Page->image['desktop']['height'] }}"
                        class="mb-8 rounded-md"
                    >
                </picture>
            @endif

            <div class="prose">{!! $Page->content !!}</div>
        </div>
        <div class="w-full md:w-1/4 md:pl-8">
            {{-- Note > 1 - there'll always by an 'All' link, so only show others if there are any --}}
            @if( isset($categories) && sizeof($categories) > 1 )
                <h3 class="text-lg font-semibold mb-4">News categories</h3>
                <ul>
                    @foreach( $categories as $category )
                        <li class="{{ !$loop->last ? 'mb-2' : '' }}">
                            <a href="{{ $category['url'] }}">{{ $category['title'] }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

@endsection

@push('schema')
@include('includes.schema.article')
@endpush