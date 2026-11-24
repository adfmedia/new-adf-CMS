@if ($paginator->hasPages())
<div class="pagination">
    <nav class="pagination--nav">
        @if( $paginator->currentPage() > 1 )
            <a 
                href="{{ $paginator->previousPageUrl() }}"
                title="Previous page"
                class="pagination--prev"
            >
                <span class="sr-only">Previous</span>
                <svg class="pagination--icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        @endif
        {{-- Mobile --}}
        <div class="pagination--mob">
            @php 
                $current_page = $paginator->currentPage();
                $prev_page = $current_page > 1 ? $current_page - 1 : false;
                $next_page = $paginator->hasMorePages() ? $current_page + 1 : false;
                $last_page_num = $paginator->lastPage();
                $show_last_dots = $last_page_num > ($current_page + 2);
                $last_page = $last_page_num > ($current_page + 1) ? $last_page_num : false;
            @endphp
            @if( $prev_page )
                <a href="{{ $paginator->url($prev_page) }}" title="Page {{ $prev_page }}" class="pagination--link">{{ $prev_page }}</a>
            @endif
            <a href="{{ $paginator->url($current_page) }}" title="Page {{ $current_page }}" class="pagination--link--current">{{ $current_page }}</a>
            @if( $next_page )
                <a href="{{ $paginator->url($next_page) }}" title="Page {{ $next_page }}" class="pagination--link">{{ $next_page }}</a>
            @endif
            @if( $last_page )
                @if( $show_last_dots )
                    <span aria-current="page" class="pagination--separator">...</span>
                @endif
                <a href="{{ $paginator->url($last_page) }}" title="Page {{ $last_page }}" class="pagination--link">{{ $last_page }}</a>
            @endif
        </div>
        {{-- Desktop --}}
        <div class="pagination--desk">
            @foreach( $elements as $element )
                @if( is_string( $element ) )
                    <span aria-current="page" class="pagination--separator">{{ $element }}</span>
                @elseif( is_array( $element ) )
                    @foreach( $element as $page => $url )
                        <a 
                            href="{{ $url }}" title="Page {{ $page }}" 
                            class="{{ $page == $paginator->currentPage() ? 'pagination--link--current' : 'pagination--link' }}"
                        >{{ $page }}</a>
                    @endforeach
                @endif
            @endforeach
        </div>
        @if( $paginator->hasMorePages() )
            <a 
                href="{{ $paginator->nextPageUrl() }}"
                title="Next page"
                class="pagination--next"
            >
                <span class="sr-only">Next</span>
                <svg class="pagination--icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        @endif
    </nav>
</div>
@endif