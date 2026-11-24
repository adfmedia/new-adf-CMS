<x-modals.empty modal_id="image-file-manager" modal_inner_size="image-picker" hide_close="true">
    @include('file-manager.partials.wrapper', ['no_margin' => true])
    <div class="flex justify-center p-4 bg-gray-100">
        <a
            data-modal-close href="#"
            class="btn btn--grey"
        >Close</a>
    </div>
</x-modals.empty>