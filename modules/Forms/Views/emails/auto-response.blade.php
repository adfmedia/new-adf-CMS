@extends('templates.email')

@section('heading', $Form->auto_response_subject)

@section('content')
    <div>
        {!! $Form->auto_response_content !!}
    </div>
@endsection