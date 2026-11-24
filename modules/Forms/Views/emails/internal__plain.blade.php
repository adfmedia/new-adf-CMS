@foreach( $FormSubmission->fields as $Field )
    {{ $Field->label }}: {{ $Field->value . PHP_EOL }}
@endforeach

---------------------------------------------------------

Submission URL: {{ $FormSubmission->submission_url . PHP_EOL }}
@if( $Form->marketing_opt_in )
Marketing opt-in: {{ $FormSubmission->marketing_opt_in ? 'Yes' : 'No' . PHP_EOL }}
@endif
@foreach( $FormSubmission->tracking_data as $TrackedData )
{{ $TrackedData->parameter }}: {{ $TrackedData->value . PHP_EOL }}
@endforeach