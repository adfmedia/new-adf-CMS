@extends('templates.login')

@section('content')

<div class="flex min-h-full items-center justify-center flex-col gap-8 py-12 px-4 sm:px-6 lg:px-8">
    <img
        class="h-16 w-16"
        src="{{ Vite::asset('resources/adm/images/adfmedia-logo.svg') }}"
        alt=""
        width="32"
        height="32"
    >
    <div class="w-full max-w-md space-y-8 bg-gray-50 p-8 rounded-lg shadow-xl">
        <div class="text-center">
            @if(isset($success) && $success)
                <div class="pt-4 mb-4 font-semibold text-sm text-green-600">
                    We've sent you an email with instructions on resetting your password.
                </div>
            @endif

            @if(isset($invalid_user) && $invalid_user )
                <div class="pt-4 mb-4 font-semibold text-sm text-red-600">Your email address was not recognised, please check the spelling and try again.</div>
            @endif
        </div>
        <form class="mt-8 space-y-6" action="{{ route('adm.forgot-password') }}" method="POST">
            @csrf
            <div class="-space-y-px rounded-md shadow-sm">
                <div>
                    <label for="email-address" class="sr-only">Email address</label>
                    <input id="email-address" name="email" type="email" autocomplete="email" autofocus required class="placeholder-input rounded-md" placeholder="Email address">
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn--with-icon w-full">
                    <span class="icon-wrap">
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Reset password
                </button>

                <div class="mt-4 text-center">
                    <a 
                    href="{{ route('adm.login') }}" 
                    class="text-sm text-primary-medium hover:underline"
                >Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection