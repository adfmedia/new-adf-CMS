@if(isset($Blocks) && $Blocks)
    @foreach( $Blocks as $Block )
        @if( view()->exists('page-builder.blocks.' . $Block->blade_file) )
            @php
                $blade_data = $Block->content;
                $blade_data['BlockObj'] = $Block;
            @endphp
            @include('page-builder.blocks.' . $Block->blade_file, $blade_data)
        @else
            <div style="background: red; color: white; padding: 1rem; font-size: 1.5rem;">BLOCK BLADE MISSING ({{ $Block->blade_file }})</div>
            @dump($Block->content);
        @endif
    @endforeach
@endif