@php
    $structure = html()->value($field_name, data_get($Item, $field_name))
@endphp

@if( !defined('FORM_STRUCTURE_PLACEHOLDERS_ADDED') )
    @php define('FORM_STRUCTURE_PLACEHOLDERS_ADDED', true); @endphp

    <div data-form-structure-placeholders class="hidden">
        <div data-placeholder="row">
            @include('crud.form-structure.partials.row')
        </div>
        <div data-placeholder="column">
            @include('crud.form-structure.partials.column')
        </div>
        <div data-placeholder="field">
            @include('crud.form-structure.partials.field')
        </div>
    </div>
@endif

<div
    data-form-structure="{{ uniqid() }}"
    data-field="{{ $field_name }}"
    class="form-structure"
>
    <div data-row-wrapper>
        @if( empty($structure) || !isset($structure['rows']) )
            @include('crud.form-structure.partials.row')
        @else
            @foreach( $structure['rows'] as $row_idx => $row )
                @include('crud.form-structure.partials.row')
            @endforeach
        @endif
    </div>
    <div class="flex items-center justify-center mt-4">
        <a data-add-field href="#" class="btn mx-2">Add field</a>
        <a data-add-row href="#" class="btn mx-2">Add row</a>
    </div>
</div>