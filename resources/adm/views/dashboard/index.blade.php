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
            <div class="bg-white shadow ring-1 ring-black/5 rounded-lg p-4 mb-8">
                <div class="border-b border-gray-200 pb-2 mb-2">
                    <h4 class="text-base font-semibold leading-6 text-gray-900">Support desk</h4>
                    <p class="mt-1 text-sm text-gray-500">For help &amp; assistance, email or call support</p>
                </div>
                <div class="flex items-center">
                    <div class="size-24 rounded-full ring-1 ring-black/5 p-2 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" preserveAspectRatio="xMidYMid meet">
                            <g transform="translate(0.000000,256.000000) scale(0.100000,-0.100000)">
                                <path fill="#003057" d="M380 2021 c0 -14 13 -21 60 -31 l60 -13 0 -697 0 -697 -60 -13 c-47 -10 -60 -17 -60 -31 0 -18 16 -19 373 -19 204 0 405 5 446 10 246 32 398 118 452 257 33 85 33 210 -1 278 -64 131 -235 223 -448 242 -40 3 -70 8 -68 10 2 3 37 9 78 14 249 31 388 150 388 330 0 154 -96 267 -280 331 -116 40 -217 48 -587 48 -338 0 -353 -1 -353 -19z m694 -70 c61 -34 107 -90 136 -168 42 -111 16 -260 -61 -347 -53 -61 -96 -77 -197 -74 l-87 3 -3 300 c-1 165 0 306 3 314 10 25 148 7 209 -28z m61 -695 c116 -50 187 -201 171 -366 -14 -143 -67 -229 -170 -280 -61 -30 -74 -33 -169 -34 l-102 -1 -3 340 c-1 187 0 346 3 354 8 20 216 10 270 -13z"/>
                                <path fill="#E61B72" d="M1916 930 c-42 -13 -90 -49 -117 -88 -20 -30 -24 -48 -24 -112 0 -66 4 -82 27 -117 33 -50 113 -93 173 -93 60 0 140 43 173 93 23 35 27 51 27 117 0 66 -4 82 -27 117 -47 71 -149 108 -232 83z"/>
                            </g>
                        </svg>
                    </div>
                    <dl class="pl-6 text-gray-600">
                        <div class="flex items-center mb-3">
                            <dt class="flex-none mr-2">
                                <span class="sr-only">Email</span>
                                <svg class="size-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </dt>
                            <dd><a href="mailto:support@bespokedigital.agency" class="hover:text-primary-medium">support@bespokedigital.agency</a></dd>
                        </div>
                        <div class="flex items-center">
                            <dt class="flex-none mr-2">
                                <span class="sr-only">Telephone</span>
                                <svg class="size-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                            </dt>
                            <dd><a href="tel:01772591100" class="hover:text-primary-medium">01772 591100</a></dd>
                        </div>
                    </dl>
                </div>
            </div>
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
