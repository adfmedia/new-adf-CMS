@php
    $stored_images = data_get($Item, $field);
    $img_to_display = null;
    if (!empty($column['listing_size']) && isset($stored_images[$column['listing_size']])) {
        $img_to_display = $stored_images[$column['listing_size']];
    }
@endphp
@if( !empty($img_to_display) && !empty($img_to_display['preview_image']) )
<div class="listing-image-wrapper{{ $img_to_display['ratio'] < 1 ? ' vertical' : '' }}">
    <img
        src="{{ $img_to_display['preview_image']['src'] }}"
        alt="{{ $img_to_display['alt'] }}"
        width="{{ $img_to_display['preview_image']['width'] }}"
        height="{{ $img_to_display['preview_image']['height'] }}"
        loading="lazy"
    />
</div>
@endif