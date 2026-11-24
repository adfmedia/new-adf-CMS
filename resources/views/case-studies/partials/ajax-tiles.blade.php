@foreach( $Articles as $Article )
    @include('case-studies.partials.listing-tile', ['Article' => $Article])
@endforeach