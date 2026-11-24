@php
    $unique_id = uniqid();
@endphp
<form
    data-cms-form="{{ $unique_id }}"
    data-label="{{ \Illuminate\Support\Str::slug($form->title) }}"
    data-recaptcha="{{ $form->use_recaptcha ? 'true' : 'false' }}"
    action="#"
    method="POST"
    enctype="multipart/form-data"
    class="relative"
>
    @csrf

    <input type="hidden" name="form_id" value="{{ $form->id }}">
    <input type="hidden" name="current_url" value="{{ url()->full() }}">

    <div
        data-field-wrap
        class=""
    >
        @include('forms.partials.structure')
    </div>
    @include('forms.partials.loader-and-alerts')
</form>