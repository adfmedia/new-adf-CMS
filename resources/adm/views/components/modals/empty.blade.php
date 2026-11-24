@props(['modal_id', 'modal_inner_size' => '', 'hide_close' => false])

<div 
    data-modal="{{ $modal_id }}"
    class="modal-wrap" role="dialog" aria-modal="true"
>
    <div class="modal-bg"></div>
    <div class="modal-position">
        <div class="modal-inner-wrap">
            <div class="modal-inner {{ $modal_inner_size }}">
                @if( !$hide_close )
                    <div class="close-wrap">
                        <button data-modal-close type="button" class="close-btn">
                            <span class="sr-only">Close</span>
                            <svg class="close-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
                <div class="">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>