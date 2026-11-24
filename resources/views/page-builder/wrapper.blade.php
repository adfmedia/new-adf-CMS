@if(isset($Blocks) && $Blocks)
    @foreach( $Blocks as $Block )
        @if( $Block->view_name && view()->exists($Block->view_name) )
            @php
                $blade_data = $Block->content;
                $blade_data['BlockObj'] = $Block;
            @endphp
            @include($Block->view_name, $blade_data)
        @elseif( $Block->view_name )
            <div style="background: red; color: white; padding: 1rem; font-size: 1.5rem;">BLOCK BLADE MISSING ({{ $Block->view_name }})</div>
            @dump($Block->content);
        @endif
    @endforeach
@endif
