@php
use \Illuminate\Support\Str;
@endphp

@extends('templates.default')

@section('content')

{{ html()->modelForm($Item, 'POST', $form_action)->attribute('data-cms-form', '')->acceptsFiles()->open() }}

    {{-- Action / summary bar --}}
    <div class="sm:flex sm:items-center mb-8 sticky top-16 -mx-4 -mt-4 bg-gray-100 p-4 shadow-xl z-10">
        <div class="sm:flex-auto">
            @if( isset($title) )
                <h1 class="text-base font-semibold leading-6 text-gray-900">{{ $title }}</h1>
            @endif
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <div class="flex items-center justify-end space-x-2">
                @if( !empty($back_button) && $back_button['enabled'] )
                    <a
                        href="{{ $back_button['url'] }}" 
                        class="btn"
                    >{{ $back_button['text'] }}</a>
                @endif
                @if( !empty($preview_button) && $preview_button['enabled'] )
                    <a
                        data-preview-btn
                        href="{{ $preview_button['url'] }}"
                        class="btn btn--orange"
                    >Preview</a>
                @endif
                @if( !empty($save_back_button) && $save_back_button['enabled']) 
                <button
                    type="submit"
                    name="save_go_back"
                    class="btn btn--save"
                >{!! $save_back_button['text'] !!}</button>
                @endif
                @if( !empty($save_continue_button) && $save_continue_button['enabled'] )
                <button
                    type="submit"
                    name="save_continue"
                    class="btn btn--save"
                >{!! $save_continue_button['text'] !!}</button>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-x-4">
        {{-- Main column --}}
        <div class="w-full">
            <div class="flow-root mb-8">
                <div class="min-w-full p-px sm:rounded-lg">
                    <div 
                        x-data="{ activeTab: 0 }"
                        class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg"
                    >
                        {{-- Tabs --}}
                        <div class="hidden sm:block">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex" aria-label="Tabs">
                                    @foreach( $form_fields as $tab => $fields )
                                        @continue($tab == 'sidebar')

                                        <a
                                            href="#"
                                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 border-b-2 py-4 px-8 text-center text-sm font-medium"
                                            :class="{ 'bg-gray-100 border-primary-medium text-primary-medium': activeTab == {{ $loop->index }}, 'border-transparent': activeTab != {{ $loop->index }} }"
                                            x-on:click="activeTab = {{ $loop->index }}"
                                        >{{ Str::title($tab) }}</a>
                                    @endforeach
                                </nav>
                            </div>
                        </div>

                        {{-- Mobile 'Tabs' (dropdown) --}}
                        <div class="grid grid-cols-1 sm:hidden mb-4">
                            <label for="mobile-section-select" class="sr-only">Change section</label>
                            <select 
                                @change="activeTab = $el.value"
                                id="mobile-section-select"
                                class="col-start-1 row-start-1 -m-px w-[calc(100%+2px)] bg-gray-100 py-2 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600"
                            >
                                @foreach( $form_fields as $tab => $fields )
                                    @continue($tab == 'sidebar')
                                    <option value="{{ $loop->index }}">{{ Str::title($tab) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tab Innards --}}
                        <div class="p-4 sm:rounded-b-lg">
                            @foreach( $form_fields as $tab => $fields )
                                @continue($tab == 'sidebar')

                                <div
                                    class="hidden divide-y divide-gray-200 space-y-3"
                                    :class="{ 'hidden': activeTab != {{ $loop->index }} }"
                                >
                                    @foreach( $fields as $field_name => $field_details )
                                        @include('crud.fields.form-field-wrap')
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if( isset($form_fields['sidebar']) )
            <div class="w-full lg:w-1/4">
                <div class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg p-4 divide-y divide-gray-200 space-y-3">
                    @foreach( $form_fields['sidebar'] as $field_name => $field_details )
                        @include('crud.fields.form-field-wrap')
                    @endforeach
                </div>

                @if( isset($latest_edit) && isset($latest_edit['edited_at']) && !empty($latest_edit['edited_at']) )
                    <div class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg p-4 mt-4 text-sm text-gray-600">
                        Last edited at {{ $latest_edit['edited_at'] }}{{ !empty($latest_edit['edited_by']) ? ' by ' . $latest_edit['edited_by'] : '' }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    @if( !isset($form_fields['sidebar']) && isset($latest_edit) && isset($latest_edit['edited_at']) && !empty($latest_edit['edited_at']) )
        <div class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg p-4 mt-4 text-sm text-gray-600">
            Last edited at {{ $latest_edit['edited_at'] }}{{ !empty($latest_edit['edited_by']) ? ' by ' . $latest_edit['edited_by'] : '' }}
        </div>
    @endif

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