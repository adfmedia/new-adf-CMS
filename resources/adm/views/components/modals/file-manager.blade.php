@props(['modal_id', 'modal_inner_size' => '', 'modal_action_class' => 'flex-row-reverse'])

<div 
    data-fm-modal="{{ $modal_id }}"
    class="modal-wrap" role="dialog" aria-modal="true"
>
    <div class="modal-bg"></div>
    <div class="modal-position">
        <div class="modal-inner-wrap">
            <div class="modal-inner {{ $modal_inner_size }}">
                <div class="close-wrap">
                    <button data-modal-close type="button" class="close-btn">
                        <span class="sr-only">Close</span>
                        <svg class="close-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="">
                    {{ $modal_content }}
                </div>
                <div class="flex{{ stristr($modal_action_class, 'mt-') === false ? ' mt-4' : '' }} {{ $modal_action_class }}">
                    {{ $modal_actions }}
                </div>
            </div>
        </div>
    </div>
</div>