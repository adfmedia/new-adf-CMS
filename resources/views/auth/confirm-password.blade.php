@extends('templates.default')

@section('content')

    <div class="container my-8 flex-col justify-center">
        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <h1 class="mb-2 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Secure area</h1>
            <p class="mb-8 text-center text-gray-500">Please confirm your password to proceed.</p>

            <div class="bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
                <form 
                    action="{{ route('password.confirm') }}" method="POST"
                    class="space-y-4"
                >
                    <div class="field-wrap">
                        @csrf
                        <label for="password" class="label">Password</label>
                        <div class="input-wrap">
                            <input 
                                id="password" name="password" type="password" autocomplete="current-password" required
                                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                                class="input"
                            >
                        </div>
                        @if( $errors->has('password') )
                            <div class="error-message">{{ $errors->first('password') }}</div>
                        @endif
                    </div>

                    <div>
                        <button type="submit" class="btn btn--primary flex w-full justify-center">Confirm password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection