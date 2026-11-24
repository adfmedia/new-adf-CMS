<div class="inline-flex items-center">
    <span 
        class="size-6 rounded-lg border border-gray-300 mr-2"
        style="background-color: {{ data_get($Item, $field) }}"
    ></span>
    {{ data_get($Item, $field) }}
</div>