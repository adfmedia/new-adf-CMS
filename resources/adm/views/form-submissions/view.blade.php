@extends('templates.default')

@section('content')

    {{-- Action / summary bar --}}
    <div class="sm:flex sm:items-center mb-8">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Submission #{{ $Submission->id }} - {{ $Form->title }}</h1>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <div class="flex items-center justify-end space-x-2">
                <a
                    href="{{ route('adm.route', ['form-submissions', $Form->id]) }}" 
                    class="btn"
                >Back</a>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="flex flex-wrap lg:flex-nowrap gap-x-4">
        {{-- Main column --}}
        <div class="w-full">
            <div class="flow-root mb-8">
                <div class="min-w-full p-px sm:rounded-lg overflow-hidden">
                    <div class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg overflow-hidden">
                        <div class="p-4 sm:rounded-b-lg divide-y divide-gray-300 space-y-6">
                            <div class="divide-y divide-gray-200 space-y-3">
                                @foreach( $Submission->fields as $Field )
                                    <div class="flex items-start{{ !$loop->last ? ' pb-3' : '' }}">
                                        <div class="w-1/5 font-semibold">{{ $Field->label }}</div>
                                        <div class="w-4/5 pl-4">
                                            @if( $Field->form_field && $Field->form_field->type == 'file')
                                                @php
                                                    $file = str_replace('cms_form_uploads/', '', $Field->value);
                                                @endphp
                                                @if( $Field->value )
                                                <a
                                                    href="{{ route('adm.route', ['form-submissions', $Submission->form_id, $Submission->id]) . '?attachment=' . $Field->form_field_id }}"
                                                    target="_blank"
                                                    class="text-primary-medium underline hover:no-underline"
                                                >View {{ $file }}</a>
                                                @else
                                                No file uploaded
                                                @endif
                                            @else
                                                {{ $Field->value }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="divide-y divide-gray-200 space-y-3">
                                <div class="flex items-start pb-3">
                                    <div class="w-1/5 font-semibold">Submission URL</div>
                                    <div class="w-4/5 pl-4">{{ $Submission->submission_url }}</div>
                                </div>
                                @foreach( $Submission->tracking_data as $TrackedData )
                                    <div class="flex items-start pb-3">
                                        <div class="w-1/5 font-semibold">{{ $TrackedData->parameter }}</div>
                                        <div class="w-4/5 pl-4">{{ $TrackedData->value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Sidebar --}}
        <div class="w-full lg:w-1/4">
            <div class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg p-4 divide-y divide-gray-200 space-y-3">
                <div class="field-wrap">
                    <div class="label">Submission date</div>
                    <div class="input-wrap text-sm">{{ $Submission->created_at->format('jS F Y, g:ia') }}</div>
                </div>
                <div class="field-wrap">
                    <div class="label">Marketing opt-in</div>
                    <div class="input-wrap text-sm">{{ $Submission->marketing_opt_in ? 'Yes' : 'No' }}</div>
                </div>
                @if( $Submission->marketing_opt_in && !empty($Submission->marketing_opt_in_text) )
                    <div class="field-wrap">
                        <div class="label">Marketing opt-in messaging</div>
                        <div class="input-wrap text-sm">{{ $Submission->marketing_opt_in_text }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection