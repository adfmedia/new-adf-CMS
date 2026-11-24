@extends('templates.default')

@section('content')

<form action="{{ $import_url }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Action / summary bar --}}
    <div class="sm:flex sm:items-center mb-8">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{ $title }}</h1>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <div class="flex items-center justify-end space-x-2">
                <a
                    href="{{ route('adm.route', ['redirects']) }}" 
                    class="btn"
                >Back</a>
                <button
                    type="submit"
                    class="btn btn--save"
                >Import</button>
            </div>
        </div>
    </div>

    {{-- Fields --}}
    <div class="flex flex-wrap lg:flex-nowrap gap-x-4">
        <div class="w-full">
            <div class="flow-root mb-8">
                <div class="min-w-full p-px sm:rounded-lg overflow-hidden">
                    <div class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg overflow-hidden">
                        <div class="p-4 sm:rounded-b-lg divide-y divide-gray-300 space-y-6">
                            <div class="divide-y divide-gray-200 space-y-3">
                                <div class="field-wrap">
                                    <label for="redirect_csv" class="label">CSV File <span class="text-red-600">*</span></label>
                                    <div class="help-text">Select the CSV file containing the redirects you'd like to upload</div>
                                    <div class="help-text">The CSV file should contain 2 or 3 columns (Old URL, New URL &amp; optional Redirect Code) &amp; a row of heading - the import will not process the first row</div>
                                    <div class="input-wrap">
                                        {{ html()->file('redirect_csv')->attributes(['required' => true, 'class' => 'input px-2']) }}
                                    </div>
                                    @if( $errors->has('redirect_csv') )
                                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('redirect_csv') }}</p>
                                    @endif
                                </div>
                                <div class="field-wrap pt-0">
                                    <label for="old_domain" class="label">Domain</label>
                                    <div class="help-text">If the redirects contain a domain, add it here to import the redirects as relative, including https://</div>
                                    <div class="input-wrap">
                                        {{ html()->text('old_domain')->attributes(['class' => 'input']) }}
                                    </div>
                                    @if( $errors->has('old_domain') )
                                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('old_domain') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection