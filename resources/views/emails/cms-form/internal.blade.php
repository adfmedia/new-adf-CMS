@extends('templates.email')

@section('heading', $Form->internal_subject)

@section('content')
    <div style="margin-bottom: 16px;">
        @foreach( $FormSubmission->fields as $Field )
            <strong>{{ $Field->label }}:&nbsp;</strong>
            {{ $Field->value }}
            @if( !$loop->last )
                <br>
            @endif
        @endforeach
    </div>
    <hr style="margin-bottom: 16px;">
    <div>
        <strong>Submission URL:&nbsp;</strong>{{ $FormSubmission->submission_url }}<br>
        @if( $Form->marketing_opt_in )
            <strong>Marketing opt-in:&nbsp;</strong>{{ $FormSubmission->marketing_opt_in ? 'Yes' : 'No' }}<br>
        @endif
        @foreach( $FormSubmission->tracking_data as $TrackedData )
            <strong>{{ $TrackedData->parameter }}:&nbsp;</strong>{{ $TrackedData->value }}<br>
        @endforeach
    </div>
@endsection