@extends('templates.default')

@section('content')

    <div class="container my-8 flex-col justify-center">
        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <h1 class="mb-8 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">{{ $Page->h1_title }}</h1>

            <div class="bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
                <form 
                    action="{{ route('password.store') }}" method="POST"
                    class="space-y-4"
                >
                    <div class="field-wrap">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <label for="email" class="label">Email address</label>
                        <div class="input-wrap">
                            <input 
                                id="email" name="email" type="email" autocomplete="email" required autofocus
                                value="{{ old('email', $request->email) }}"
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
                        <button type="submit" class="btn btn--primary flex w-full justify-center">Reset password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection