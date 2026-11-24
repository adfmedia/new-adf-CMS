<div
    data-structure-row="{{ isset($row_idx) ? $row_idx : 0 }}"
    class="structure-row"
>
    <div 
        data-row-handle
        class="move-handle"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
        </svg>
    </div>
    <div class="actions-wrap">
        <a data-remove-row href="#" class="act-link remove-row">&minus; Remove row</a>
        <a data-add-col href="#" class="act-link add-col">&plus; Add column</a>
    </div>
    <div 
        data-row-columns 
        class="row-columns"
    >
        @if( !isset($row) || !isset($row['columns']) )
            @include('crud.form-structure.partials.column')
        @else
            @foreach( $row['columns'] as $col_idx => $column )
                @include('crud.form-structure.partials.column')
            @endforeach
        @endif
    </div>
</div>