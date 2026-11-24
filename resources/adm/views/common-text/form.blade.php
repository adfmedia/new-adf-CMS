@extends('templates.default')

@section('content')

{{ html()->modelForm($FormModel, 'POST', $form_action)->open() }}

    <div class="sm:flex sm:items-center mb-8">
        <div class="sm:flex-auto">
            @if( isset($title) )
                <h1 class="text-base font-semibold leading-6 text-gray-900">{{ $title }}</h1>
            @endif
            @if( isset($description) && !empty($description) )
                <p class="mt-2 text-sm text-gray-700">{{ $description }}</p>
            @endif
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <div class="flex items-center justify-end space-x-2">
                <button
                    type="submit"
                    class="btn btn--save"
                >Save</button>    
            </div>
        </div>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-x-4">
        <div class="w-full">
            <div class="flow-root mb-8">
                <div class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg p-4 divide-y divide-gray-200 space-y-4">
                    @foreach( $fields as $group_label => $group_fields )
                        <div class="{{ !$loop->last ? 'pb-4' : '' }}">
                            <h3 class="text-lg font-semibold">{{ $group_label }}</h3>
                            <div class="divide-y divide-gray-200 space-y-3">
                                @foreach( $group_fields as $field_name => $field_details )
                                    <div class="field-wrap">
                                    <label for="{{ $field_name }}" class="label">{{ $field_details['label'] }}</label>
                                    @if( !empty($field_details['help']) )
                                        <div class="help-text">{{ $field_details['help'] }}</div>
                                    @endif
                                    <div class="input-wrap">
                                        @if( view()->exists('crud.fields.form.' . $field_details['type']) )
                                            @include('crud.fields.form.' . $field_details['type'])
                                        @else
                                            <div class="text-red-600 font-bold">Field blade missing for {{ $field_details['type'] }}</div>
                                        @endif
                                    </div>
                                    @if( $errors->has($field_name) )
                                        <p class="mt-2 text-sm text-red-600">{{ $errors->first($field_name) }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if( false )
            <div class="w-full lg:w-1/4">
            </div>
        @endif
    </div>

{{ html()->closeModelForm() }}

{{-- Modal wrapper --}}
<div data-form-modals>
    @include('crud.includes.image-file-manager-modal')
    @include('crud.includes.link-browse-modal')
</div>

@endsection

@push('footer_scripts')
@vite('resources/adm/js/file-manager.js')
@include('crud.includes.wysiwyg-scripts')
@endpush