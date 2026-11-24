@extends('templates.default')

@section('content')

    <div class="flex flex-wrap mt-8">
        @if( $forms_enabled )
            <div class="w-full sm:w-1/3 sm:pr-4">
                <div class="bg-white shadow ring-1 ring-black/5 rounded-lg p-4">
                    <div class="border-b border-gray-200 pb-2 mb-2">
                        <h4 class="text-base font-semibold leading-6 text-gray-900">Latest form submissions</h4>
                        <p class="mt-1 text-sm text-gray-500">See your latest enquiries</p>
                    </div>
                    <div class="divide-y divide-gray-300 space-y-2">
                        @foreach( $form_submissions as $submission )
                            <div class="text-sm pb-2">
                                <div class="flex justify-between">
                                    <span class="font-semibold">{{ $submission['form'] }}</span>
                                    <span class="text-xs">{{ $submission['date'] }}</span>
                                </div>
                                @if( !empty($submission['fields']) )
                                    <div class="mt-1 text-sm text-gray-600">{{ implode(', ', $submission['fields']) }}</div>
                                @endif
                                <div class="mt-1 text-sm flex justify-end">
                                    <a href="{{ $submission['url'] }}" class="text-primary-medium hover:underline">View details</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <div class="w-full sm:w-1/3 sm:px-4">
            <div class="bg-white shadow ring-1 ring-black/5 rounded-lg p-4">
                <div class="border-b border-gray-200 pb-2 mb-2">
                    <h4 class="text-base font-semibold leading-6 text-gray-900">Latest edits</h4>
                    <p class="mt-1 text-sm text-gray-500">See what's been recently changed</p>
                </div>
                <div class="divide-y divide-gray-300 space-y-2">
                    @foreach( $latest_changes as $change )
                        <div class="text-sm pb-2">
                            <div class="flex justify-between">
                                @if( $change['has_url'] )
                                    <a href="{{ $change['url'] }}" class="font-semibold text-primary-medium hover:underline">
                                        {{ $change['title'] }}
                                    </a>
                                @else
                                    <span class="font-semibold">{{ $change['title'] }}</span>
                                @endif
                                <div class="ml-2">{{ $change['section'] }}</div>
                            </div>
                            <div class="mt-1 flex justify-between text-gray-600">
                                <div>{{ $change['user'] }}</div>
                                <div class="ml-2">{{ $change['type'] . ' at ' . $change['date'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="w-full sm:w-1/3 sm:pl-4">
            <div class="bg-white shadow ring-1 ring-black/5 rounded-lg p-4">
                <div class="border-b border-gray-200 pb-2 mb-2">
                    <h4 class="text-base font-semibold leading-6 text-gray-900">Support details</h4>
                    <p class="mt-1 text-sm text-gray-500">Your fast response support package includes</p>
                </div>
                <dl class="text-gray-600">
                    @php
                        $points = [
                            'Technical support (Mon-Fri 9am to 5:30pm)',
                            'One hour fast response promise',
                            'In-house technical support team',
                        ];
                    @endphp
                    @foreach( $points as $point )
                        <div class="flex items-center mb-3">
                            <dt class="flex-none mr-2">
                                <svg class="size-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </dt>
                            <dd>{{ $point }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>

@endsection
