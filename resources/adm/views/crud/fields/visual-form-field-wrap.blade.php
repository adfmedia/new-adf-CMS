<div 
    data-field="{{ $field_name }}"
@if( isset($field_details['toggles']) && !empty($field_details['toggles']) )
    data-toggles="{{ json_encode($field_details['toggles']) }}"
@endif
    class="field-wrap{{ $field_details['type'] == 'hidden' ? ' hidden' : '' }}"
>
    <label for="{{ $field_name }}" class="label">
        {{ $field_details['label'] }}
        @if( $field_details['required'] )
            <span class="text-red-600">*</span>
        @endif
    </label>
    @if( !empty($field_details['help']) )
        <div class="help-text">{{ $field_details['help'] }}</div>
    @endif
    <div class="input-wrap">
        @if( view()->exists('crud.fields.visual-form.' . $field_details['type']) )
            @include('crud.fields.visual-form.' . $field_details['type'])
        @elseif( view()->exists('crud.fields.form.' . $field_details['type']) )
            @include('crud.fields.form.' . $field_details['type'])
        @else
            <div class="text-red-600 font-bold">Field blade missing for {{ $field_details['type'] }}</div>
        @endif
    </div>
    @if( $errors->has($field_name) )
        <p class="mt-2 text-sm text-red-600">{{ $errors->first($field_name) }}</p>
    @endif
</div>