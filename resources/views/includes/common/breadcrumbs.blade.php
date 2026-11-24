@if( isset( $breadcrumbs ) && !empty( $breadcrumbs ) )
    <nav aria-label="Breadcrumb" class="container py-3">
        <ol class="breadcrumbs flex gap-2 text-xs md:text-sm font-medium">
            @foreach( $breadcrumbs as $crumb )
                <li class="">
                    @if( !$loop->first )
                        <svg class="size-4  mr-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    @endif
                    <div class="truncate">
                        <a href="{{ $crumb['url'] }}">{{ $crumb['text'] }}</a>
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>
@endif
