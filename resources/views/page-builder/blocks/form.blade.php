@if( isset($form) && $form && $form->structure )
<div data-block="form" class="mb-8">
    @if( !empty($form->template) && view()->exists('forms.templates.' . $form->template) )
        @include('forms.templates.' . $form->template)
    @else
        @include('forms.templates.default')
    @endif
</div>
@endif