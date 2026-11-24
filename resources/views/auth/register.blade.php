@extends('templates.default')

@section('content')

    <div class="container my-8 flex-col justify-center">
        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <h1 class="mb-8 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">{{ $Page->h1_title }}</h1>

            <div class="bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
                <form 
                    action="{{ route('register') }}" method="POST"
                    class="space-y-4"
                >
                    <div class="field-wrap">
                        @csrf
                        <label for="name" class="label">Your name</label>
                        <div class="input-wrap">
                            <input 
                                id="name" name="name" type="text" autocomplete="name" required autofocus
                                class="input"
                            >
                        </div>
                        @if( $errors->has('name') )
                            <div class="error-message">{{ $errors->first('name') }}</div>
                        @endif
                    </div>
                    <div class="field-wrap">
                        <label for="email" class="label">Email address</label>
                        <div class="input-wrap">
                            <input 
                                id="email" name="email" type="email" autocomplete="email" required
                                class="input"
                            >
                        </div>
                        @if( $errors->has('email') )
                            <div class="error-message">{{ $errors->first('email') }}</div>
                        @endif
                    </div>
                    <div class="field-wrap">
                        <label for="password" class="label">Password</label>
                        <div class="input-wrap">
                            <input 
                                id="password" name="password" type="password" autocomplete="new-password" required
                                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                                class="input"
                            >
                        </div>
                        @if( $errors->has('password') )
                            <div class="error-message">{{ $errors->first('password') }}</div>
                        @endif
                    </div>
                    <div class="field-wrap">
                        <label for="confirm-password" class="label">Confirm password</label>
                        <div class="input-wrap">
                            <input 
                                id="confirm-password" name="password_confirmation" type="password" autocomplete="new-password" required
                                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                                class="input"
                            >
                        </div>
                        @if( $errors->has('password_confirmation') )
                            <div class="error-message">{{ $errors->first('password_confirmation') }}</div>
                        @endif
                    </div>

                    <div>
                        <button type="submit" class="btn btn--primary flex w-full justify-center">Register</button>
                    </div>
                </form>
            </div>

            @if(Route::has('login'))
                <p class="mt-10 text-center text-sm text-gray-500">
                    Already have an account?
                    <a 
                        href="{{ route('login') }}" 
                        class="font-semibold leading-6 text-primary hover:underline"
                    >Login now</a>
                </p>
            @endif
        </div>
    </div>

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection