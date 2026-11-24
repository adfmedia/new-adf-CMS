@php
use \Illuminate\Support\Str;
@endphp

@extends('templates.default')

@section('content')

{{ html()->modelForm($Item, 'POST', '#')->open() }}

    {{-- Action / summary bar --}}
    <div class="sm:flex sm:items-center mb-8">
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
            </div>
        </div>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-x-4">
        {{-- Main column --}}
        <div class="w-full">
            <div class="flow-root mb-8">
                <div class="min-w-full p-px sm:rounded-lg overflow-hidden">
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

                        {{-- Tab Innards --}}
                        <div class="p-4 sm:rounded-b-lg">
                            @foreach( $form_fields as $tab => $fields )
                                @continue($tab == 'sidebar')

                                <div
                                    class="hidden divide-y divide-gray-200 space-y-3"
                                    :class="{ 'hidden': activeTab != {{ $loop->index }} }"
                                >
                                    @foreach( $fields as $field_name => $field_details )
                                        <div class="field-wrap">
                                            <label for="{{ $field_name }}" class="label">{{ $field_details['label'] }}</label>
                                            @if( !empty($field_details['help']) )
                                                <div class="help-text">{{ $field_details['help'] }}</div>
                                            @endif
                                            <div class="input-wrap">
                                                @if( view()->exists('crud.fields.read.' . $field_details['type']) )
                                                    @include('crud.fields.read.' . $field_details['type'])
                                                @else
                                                    <div class="text-red-600 font-bold">Read blade missing for {{ $field_details['type'] }}</div>
                                                @endif
                                            </div>
                                        </div>
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
                        <div class="field-wrap">
                            <label for="{{ $field_name }}" class="label">{{ $field_details['label'] }}</label>
                            @if( !empty($field_details['help']) )
                                <div class="help-text">{{ $field_details['help'] }}</div>
                            @endif
                            <div class="input-wrap">
                                @if( view()->exists('crud.fields.read.' . $field_details['type']) )
                                    @include('crud.fields.read.' . $field_details['type'])
                                @else
                                    <div class="text-red-600 font-bold">Read blade missing for {{ $field_details['type'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

{{ html()->closeModelForm() }}

@endsection