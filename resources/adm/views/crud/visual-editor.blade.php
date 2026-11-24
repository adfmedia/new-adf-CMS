@php
use \Illuminate\Support\Str;
@endphp

@extends('templates.visual-editor')

@section('content')

@if(session('success'))
    <div data-flash-msg class="flash-msg success">{{ session('success') }}</div>
    @if( isset($broadcast_channel) )
        <script @cspNonce>const FLASH_BROADCAST = '{{ $broadcast_channel }}';</script>
    @endif
@elseif(session('error'))
    <div data-flash-msg class="flash-msg error">{{ session('error') }}</div>
@endif

{{ html()->modelForm($Item, 'POST', $form_action)->attribute('data-cms-form', '')->attribute('id', 'cms_form')->acceptsFiles()->open() }}

    <div class="divide-y divide-gray-200 visual-editor-sidebar">
        {{-- Accordion groups --}}
        @foreach( $form_fields as $tab => $fields )
            <div 
                x-data="{ expanded: true }"
                class="py-6 first:pt-0 last:pb-0"
                :aria-expanded="expanded"
            >
                <a
                    @click.prevent="expanded = !expanded"
                    href="#"
                    class="w-full flex items-start justify-between text-left text-gray-900"
                >
                    <span class="text-base/7 font-semibold">{{ Str::title($tab) }}</span>
                    <span class="ml-6 flex h-7 items-center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 in-aria-expanded:hidden">
                            <path d="M12 6v12m6-6H6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 not-in-aria-expanded:hidden">
                            <path d="M18 12H6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>
                <div
                    class="flex flex-col divide-y divide-gray-200 pt-6"
                    :class="{ 'hidden': !expanded, 'flex': expanded }"
                >
                    @foreach( $fields as $field_name => $field_details )
                        @include('crud.fields.visual-form-field-wrap')
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

{{ html()->closeModelForm() }}

{{-- Modal wrapper --}}
<div data-form-modals>
    @include('crud.includes.image-file-manager-modal')
    @include('crud.includes.image-crop-modal')
    @include('crud.includes.link-browse-modal')
</div>
@endsection

@push('footer_scripts')
@vite('resources/adm/js/file-manager.js')
@include('crud.includes.wysiwyg-scripts')
@endpush