@php
    $field_attrs = ['class' => 'input with-price-prefix', 'id' => $field_name];
    if ($field_details['required']) {
        $field_attrs['required'] = '';
    }

    $dps = 2;
    $symbol = "£";
    if (!empty($field_details['price_format'])) {
        if (isset($field_details['price_format']['decimals'])) {
            $dps = $field_details['price_format']['decimals'];
        }
        if (isset($field_details['price_format']['currency_symbol'])) {
            $symbol = $field_details['price_format']['currency_symbol'];
        }
    }
    $field_attrs['placeholder'] = number_format(0, $dps);
@endphp
<div class="relative rounded-md">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <span class="text-gray-500 sm:text-sm">{{ $symbol }}</span>
    </div>
    {{ html()->text($field_name)->attributes($field_attrs) }}
</div>