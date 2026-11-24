<x-modals.empty modal_id="menu-builder-edit-details">
    <h3 class="text-base font-semibold leading-6 text-gray-900">Edit {{ $Menu->title }} details</h3>
    <p class="text-sm">Please note any changes made here will refresh the page, so if you have edited the menu save it first.</p>
    
    <form data-edit-form action="" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="menu" value="{{ $Menu->menu_id }}" />

        <div class="divide-y divide-gray-200 space-y-3">
            <div class="field-wrap">
                <label for="menu-edit-title" class="label">{{ $fields['title']['label'] }}</label>
                <div class="help-text">{{ $fields['title']['help'] }}</div>
                <div class="input-wrap">
                    <input type="text" name="title" id="menu-edit-title" value="{{ $Menu->title }}" required class="input" />
                </div>
            </div>
            <div class="field-wrap">
                <label for="menu-edit-reference" class="label">{{ $fields['code_reference']['label'] }}</label>
                <div class="help-text">{{ $fields['code_reference']['help'] }}</div>
                <div class="input-wrap">
                    <input type="text" name="code_reference" id="menu-edit-reference" value="{{ $Menu->code_reference }}" required class="input" />
                </div>
            </div>
            <div class="field-wrap">
                <label for="menu-edit-nest_limit" class="label">{{ $fields['nest_limit']['label'] }}</label>
                <div class="help-text">{{ $fields['nest_limit']['help'] }}</div>
                <div class="input-wrap">
                    <input type="number" name="nest_limit" id="menu-edit-nest_limit" value="{{ $Menu->nest_limit }}" required class="input" />
                </div>
            </div>
        </div>
        
        <div class="flex mt-4">
            <a
                data-confirm-btn href="#"
                class="btn btn--save mr-4"
            >Update details</a>
            <a
                data-modal-close href="#"
                class="btn btn--grey"
            >Cancel</a>
        </div>
    </form>
</x-modals.empty>