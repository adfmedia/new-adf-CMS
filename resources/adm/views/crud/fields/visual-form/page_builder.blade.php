@php
    $Blocks = data_get($Item, $field_name);
    $random_id = uniqid();

    $add_btn = true;
    if(data_get($field_details, 'max') && count($Blocks) >= $field_details['max']) {
        $add_btn = false;
    }
@endphp
<div
    data-page-builder="{{ $field_name }}" data-id="{{ $random_id }}"
    data-block-filter="{{ isset($field_details['block_filter']) && !empty($field_details['block_filter']) ? $field_details['block_filter'] : '' }}"
    data-item-class="{{ get_class($Item) }}"
    {!! isset($field_details['max']) && $field_details['max'] ? ' data-max="' . $field_details['max'] . '"' : '' !!}
    class="page-builder-field visual-editor"
>
    <div data-blocks>
        @foreach( $Blocks as $Block )
            @include(
                'crud.blocks.visual-editor-wrapper',
                [
                    'is_new'        => false,
                    'field_name'    => $field_name . '[' . $Block->sort_order . ']',
                    'field_prefix'  => $field_name . '[' . $Block->sort_order . ']' . '[content]',
                    'sort_order'    => $Block->sort_order,
                    'status'        => $Block->status,
                    'block'         => (array)$Block->render_settings,
                    'fields'        => (array)$Block->form_fields,
                    'CommonBlock'   => $Block->commonBlock(),
                    'is_open'       => data_get($field_details, 'max') === 1
                ]
            )
        @endforeach
    </div>

    {{-- Add block --}}
    <div class="flex mt-4">
        <a
            data-add-block
            href="#"
            class="btn {{ !$add_btn ? 'hidden' : '' }}"
        >Add block</a>
    </div>
    @include('crud.includes.page-builder-modal', ['pb_id' => $random_id])
</div>