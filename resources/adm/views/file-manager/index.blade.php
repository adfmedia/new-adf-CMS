@extends('templates.default')

@section('content')

    @include('file-manager.partials.wrapper', ['wrapper_type' => 'full', 'auto_init' => true])

@endsection

@push('footer_scripts')
@vite('resources/adm/js/file-manager.js')
@endpush