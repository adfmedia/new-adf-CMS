@extends('templates.default')

@section('content')

    <div class="container my-8 flex-col justify-center">
        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <h1 class="mb-2 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">{{ $Page->h1_title }}</h1>
            <p class="mb-8 text-center text-gray-500">No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</p>

            <div class="bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
                @if( session()->has('status') )
                    <div class="bg-green-600 text-white p-4 text-center rounded-lg mb-8">{{ session()->get('status') }}</div>
                @endif
                
                <form 
                    action="{{ route('password.email') }}" method="POST"
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

                    <div>
                        <button type="submit" class="btn btn--primary flex w-full justify-center">Email reset link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection