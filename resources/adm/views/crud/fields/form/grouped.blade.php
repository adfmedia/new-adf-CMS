<div
    data-grouped-field="{{ $field_name }}"
    class="divide-y divide-gray-200 space-y-3 -mt-4"
>
    @foreach( $field_details['fields'] as $sub_field_name => $sub_field_details )
        @php 
            $sub_field = $field_name . '[' . $sub_field_name . ']';
            $sub_field_dot = $field_name . '.' . $sub_field_name;
        @endphp
        <div class="field-wrap">
            <div class="label">{{ $sub_field_details['label'] }}</div>
            @if( !empty($sub_field_details['help']) )
                <div class="help-text">{{ $sub_field_details['help'] }}</div>
            @endif
            <div class="input-wrap">
                @if( view()->exists('crud.fields.form.' . $sub_field_details['type']) )
                    @include('crud.fields.form.' . $sub_field_details['type'], ['field_name' => $sub_field, 'field_details' => $sub_field_details])
                @else
                    <div class="text-red-600 font-bold">Field blade missing for {{ $sub_field_details['type'] }}</div>
                @endif
            </div>
            @if( $errors->has($sub_field_dot) )
                <p class="mt-2 text-sm text-red-600">{{ $errors->first($sub_field_dot) }}</p>
            @endif
        </div>
    @endforeach
</div>