@extends('templates.default')

@section('content')
    
    <div class="container py-4">
        <h1 class="text-heading-1 mb-8">{{ $Page->h1_title }}</h1>

        @if( session()->has('status') )
            <div class="bg-green-600 text-white p-4 text-center rounded-lg mb-8">{{ session()->get('status') }}</div>
        @endif
    </div>

    <div class="container flex flex-wrap items-stretch mb-8">
        <div class="w-full md:w-1/2 md:pr-8">
            <div class="h-full bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12 space-y-6">
                <h4 class="mb-2 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Your details</h4>
                <form action="{{ $Page->url }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                            <div class="mt-2">
                                <input 
                                    id="email" name="email" type="email" readonly
                                    value="{{ $User->email }}"
                                    class="block w-full rounded-md border-0 py-1.5 bg-gray-100 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 sm:text-sm sm:leading-6"
                                >
                            </div>
                            <p class="mt-1 text-sm text-gray-500">If you need to change your email address, please contact us.</p>
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Your name</label>
                            <div class="mt-2">
                                <input 
                                    id="name" name="name" type="text" autocomplete="name" required
                                    value="{{ $User->name }}"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6"
                                >
                            </div>
                            @if( $errors->has('name') )
                                <div class="mt-1 text-red-600">{{ $errors->first('name') }}</div>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center justify-between">
                            <button type="submit" class="btn btn--primary">Update my details</button>
                            <a 
                                data-lightbox data-group="{{ uniqid() }}" 
                                href="#delete-account-form" 
                                class="btn btn--red"
                            >Delete my account</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="w-full md:w-1/2 md:pl-8">
            <div class="h-full bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12 space-y-6">
                <h4 class="mb-2 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Update your password</h4>
                <form action="{{ $Page->url }}" method="POST">
                    @csrf
                    <input type="hidden" name="pw_form" value="1">

                    <div class="space-y-6">
                        <div>
                            <label for="current-password" class="block text-sm font-medium leading-6 text-gray-900">Current password</label>
                            <div class="mt-2">
                                <input 
                                    id="current-password" name="current_password" type="password" autocomplete="current-password" required
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6"
                                >
                            </div>
                            @if( $errors->has('current_password') )
                                <div class="mt-1 text-red-600">{{ $errors->first('current_password') }}</div>
                            @endif
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium leading-6 text-gray-900">New password</label>
                            <div class="mt-2">
                                <input 
                                    id="password" name="password" type="password" autocomplete="new-password" required
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6"
                                >
                            </div>
                            @if( $errors->has('password') )
                                <div class="mt-1 text-red-600">{{ $errors->first('password') }}</div>
                            @endif
                        </div>
                        <div>
                            <label for="confirm-password" class="block text-sm font-medium leading-6 text-gray-900">Confirm new password</label>
                            <div class="mt-2">
                                <input 
                                    id="confirm-password" name="password_confirmation" type="password" autocomplete="new-password" required
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6"
                                >
                            </div>
                            @if( $errors->has('password_confirmation') )
                                <div class="mt-1 text-red-600">{{ $errors->first('password_confirmation') }}</div>
                            @endif
                        </div>
                        <div>
                            <button type="submit" class="btn btn--primary">Change my password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

    {{-- Hidden delete form --}}
    <form 
        id="delete-account-form"
        action="{{ $Page->url }}" method="POST"
        class="hidden text-center"
    >
        @csrf
        <input type="hidden" name="delete_account" value="1">

        <p class="text-lg leading-9 text-gray-900 mb-3">Are you sure you want to delete your account?</p>
        <p class="text-lg font-semibold leading-9 text-gray-900 mb-6">This process is not reversible.</p>
        <div>
            <button type="submit" class="btn btn--red">I understand, delete my account.</button>
        </div>
    </form>

@endsection