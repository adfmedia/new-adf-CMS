<div class="container">
    <h3 class="text-xl mb-4">{{ !empty($form->heading) ? $form->heading : $form->title }}</h3>
    @if( !empty($form->content) )
        <div class="prose sm:prose-lg mb-4">{!! $form->content !!}</div>
    @endif

    @include('forms.partials.form-wrap')
</div>