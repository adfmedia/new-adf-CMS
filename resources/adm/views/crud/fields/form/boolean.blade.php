{{ html()->hidden($field_name, 0) }}
<button
    x-data="{ checked: {{ html()->value($field_name, data_get($Item, $field_name)) ? 'true' : 'false' }} }"
    @click.prevent="checked = !checked"
    :aria-checked="checked ? true : false"
    type="button"
    class="toggle"
>
    {{ html()->checkbox($field_name, null, 1)->attributes(['class' => 'hidden', 'id' => $field_name, ':checked' => 'checked', 'x-model.fill' => 'checked']) }}
    <span class="sr-only">Use setting</span>
    <span 
        class="pointer-events-none relative inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
        :class="{ 'translate-x-5': checked, 'translate-x-0': !checked }"
    >
        <span 
            class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity" 
            :class="{ 'opacity-0 duration-100 ease-out': checked, 'opacity-100 duration-200 ease-in': !checked }"
            aria-hidden="true"
        >
            <svg class="size-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
        <span 
            class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity" 
            :class="{ 'opacity-100 duration-200 ease-in': checked, 'opacity-0 duration-100 ease-out': !checked }"
            aria-hidden="true"
        >
            <svg class="size-3 active-toggle" fill="currentColor" viewBox="0 0 12 12">
                <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
            </svg>
        </span>
    </span>
</button>