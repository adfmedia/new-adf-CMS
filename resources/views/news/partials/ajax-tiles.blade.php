@foreach( $Articles as $Article )
    @include('news.partials.listing-tile', ['Article' => $Article])
@endforeach