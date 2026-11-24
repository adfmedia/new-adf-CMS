@extends('templates.default')

@section('content')

    <div class="container py-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach( $groups as $group_title => $GroupURLs )
            <div class="">
                <h3 class="text-xl mb-4">{{ $group_title }}</h3>
                <ul>
                    @foreach( $GroupURLs as $SitemapPage )
                        <li>
                            <a href="{{ $SitemapPage->url }}" class="text-primary-600 hover:underline">{{ $SitemapPage->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection