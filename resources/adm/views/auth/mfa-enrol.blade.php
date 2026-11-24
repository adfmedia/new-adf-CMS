@extends('templates.login')

@section('content')

<div class="flex min-h-full items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8 bg-gray-50 p-8 rounded-lg shadow-xl">
        <form 
            data-mfa-form="{{ route('adm.mfa-enrol') }}"
            class="" action="#" method="POST"
        >
            @csrf
            <input type="hidden" name="user_id" value="{{ $user_id }}">
            <input type="hidden" name="secret" value="{{ $secret }}">

            <h3 class="text-center text-lg font-semibold mb-4">Two-factor authentication</h3>
            <div class="text-center mb-6">Please scan the QR code below in your authenticator app, enter your code &amp; click "Verify".</div>
            <div class="flex justify-center mb-6">{!! $qr_svg !!}</div>
            <div class="mb-6">
                <label for="verify" class="sr-only">Enter your code</label>
                <input id="verify" name="verify" type="text" autocomplete="none" autofocus required class="placeholder-input rounded-md" placeholder="Enter your code">
            </div>
            <div>
                <button type="submit" class="btn btn--with-icon w-full">
                    <span class="icon-wrap">
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Verify
                </button>
            </div>
            <div data-error class="mt-4 text-red-600 text-sm text-center font-semibold hidden"></div>
            <div data-success class="mt-4 text-green-600 text-sm text-center font-semibold hidden">Code verified.</div>
        </form>
    </div>
</div>

@endsection

@push('footer_scripts')
@vite('resources/adm/js/mfa.js')
@endpush