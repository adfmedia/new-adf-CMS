@extends('templates.default')

@section('content')

    @include('page-builder.wrapper', ['Blocks' => $Page->page_builder])

@endsection

@push('schema')
@include('includes.schema.site-search')
@endpush