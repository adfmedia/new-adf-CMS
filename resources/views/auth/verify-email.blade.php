@extends('templates.default')

@section('content')

    <div class="container my-8 flex-col justify-center">
        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <h1 class="mb-2 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Thank you</h1>
            <p class="mb-8 text-center text-gray-500">Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.</p>

            <div class="bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12 space-y-6">
                @if( session('status') == 'verification-link-sent' )
                    <div class="bg-green-600 text-white p-4 text-center rounded-lg mb-8">A new verification link has been sent to the email address you provided during registration.</div>
                @endif
                <form action="{{ route('verification.send') }}" method="POST">
                    @csrf
                    <div>
                        <button type="submit" class="btn btn--primary flex w-full justify-center">Re-send verification email</button>
                    </div>
                </form>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <div>
                        <button type="submit" class="btn btn--primary flex w-full justify-center">Log out</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection