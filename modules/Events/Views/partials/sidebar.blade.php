{{-- Note > 1 - there'll always by an 'All' link, so only show others if there are any --}}
@if( isset($categories) && sizeof($categories) > 1 )
    <h3 class="text-lg font-semibold mb-4">Categories</h3>
    <ul class="mb-8">
        @foreach( $categories as $category )
            <li class="{{ !$loop->last ? 'mb-2' : '' }}">
                <a 
                    href="{{ $category['url'] }}"
                    class="{{ $category['active'] ? 'font-semibold underline' : 'hover:underline'}}"
                >{{ $category['title'] }}</a>
            </li>
        @endforeach
    </ul>
@endif

@if( isset($months) && sizeof($months) > 1 )
    <h3 class="text-lg font-semibold mb-4">Months</h3>
    <ul class="mb-8">
        @foreach( $months as $month )
            <li class="{{ !$loop->last ? 'mb-2' : '' }}">
                <a 
                    href="{{ $month['url'] }}"
                    class="{{ $month['active'] ? 'font-semibold underline' : 'hover:underline'}}"
                >{{ $month['title'] }}</a>
            </li>
        @endforeach
    </ul>
@endif