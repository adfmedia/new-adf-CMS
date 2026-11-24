@extends('templates.default')

@section('content')
    
    <div class="container py-4">
        <h1 class="text-heading-1">{{ $Page->h1_title }}</h1> 
    </div>

    @if( isset($TeamMembers) && $TeamMembers->isNotEmpty() )
        <div class="container mb-8">
            <div class="mx-auto grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                @foreach( $TeamMembers as $TeamMember )
                    @include('team-members.partials.listing-tile-with-description')
                @endforeach
            </div>
        </div>
    @endif

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection