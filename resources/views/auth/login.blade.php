@extends('templates.default')

@section('content')

    <div class="container my-8 flex-col justify-center">
        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <h1 class="mb-8 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">{{ $Page->h1_title }}</h1>

            <div class="bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
                @if( session()->has('status') )
                    <div class="bg-green-600 text-white p-4 text-center rounded-lg mb-8">{{ session()->get('status') }}</div>
                @endif

                <form 
                    action="{{ route('login') }}" method="POST"
                    class="space-y-4"
                >
                    <div class="field-wrap">
                        @csrf
                        <label for="email" class="label">Email address</label>
                        <div class="input-wrap">
                            <input 
                                id="email" name="email" type="email" autocomplete="email" required autofocus
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
                                id="password" name="password" type="password" autocomplete="current-password" required
                                class="input"
                            >
                        </div>
                        @if( $errors->has('password') )
                            <div class="error-message">{{ $errors->first('password') }}</div>
                        @endif
                    </div>

                    <div class="field-wrap flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember-me" name="remember" type="checkbox" 
                                class="size-4 rounded-sm border-gray-300 text-primary focus:ring-primary"
                            >
                            <label for="remember-me" class="ml-3 block text-sm leading-6 text-gray-900">Remember me</label>
                        </div>

                        @if (Route::has('password.request'))
                            <div class="text-sm leading-6">
                                <a href="{{ route('password.request') }}" class="font-semibold text-primary hover:underline">Forgot password?</a>
                            </div>
                        @endif
                    </div>

                    <div>
                        <button type="submit" class="btn btn--primary flex w-full justify-center">Log in</button>
                    </div>
                </form>
            </div>

            @if(Route::has('register'))
                <p class="mt-10 text-center text-sm text-gray-500">
                    Don't have an account?
                    <a 
                        href="{{ route('register') }}" 
                        class="font-semibold leading-6 text-primary hover:underline"
                    >Register now</a>
                </p>
            @endif
        </div>
    </div>

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection