@php
    $meta_description = data_get($Item, $field_name . '.meta_description');
    $is_super_admin = \Illuminate\Support\Facades\Auth::guard('adm')->user()->is_super_admin;

    $url_gen_field = '';
    if (isset($field_details['url_generation_field'])) {
        $url_gen_field = is_array($field_details['url_generation_field']) ? $field_details['url_generation_field'] : [$field_details['url_generation_field']];
        $url_gen_field = json_encode($url_gen_field);
    }
    $meta_title_gen_field = '';
    if (isset($field_details['meta_title_gen'])) {
        $meta_title_gen_field = is_array($field_details['meta_title_gen']) ? $field_details['meta_title_gen'] : [$field_details['meta_title_gen']];
        $meta_title_gen_field = json_encode($meta_title_gen_field);
    }
    $meta_description_gen_field = '';
    if (isset($field_details['meta_description_gen'])) {
        $meta_description_gen_field = is_array($field_details['meta_description_gen']) ? $field_details['meta_description_gen'] : [$field_details['meta_description_gen']];
        $meta_description_gen_field = json_encode($meta_description_gen_field);
    }

    $allow_slashes = isset($field_details['allow_slashes']) && $field_details['allow_slashes'];

    $url_exists = isset($Item) && $Item ? (int)$Item->url_record()->exists() : 0;
@endphp
<div
    data-url-path-field
    data-url-gen-field="{{ $url_gen_field }}"
    data-meta-title-gen-field="{{ $meta_title_gen_field }}"
    data-meta-desc-gen-field="{{ $meta_description_gen_field }}"
    data-allow-slashes="{{ $allow_slashes ? 'true' : 'false' }}"
    x-data="{
        expanded: false, 
        url: '', 
        meta_title: '', 
        meta_description: '{{ addslashes(str_replace(["\r", "\n", "\r\n", PHP_EOL], ' ', $meta_description)) }}',

        system_page_expanded: true,

        formattedUrl() {
            let formatted_url = this.url;
            formatted_url = formatted_url.replaceAll('&', '-and-');
            formatted_url = formatted_url.replaceAll('--', '-');
            formatted_url = formatted_url.replaceAll(/[{{ $allow_slashes ? '^-\w\d\s\/' : '^-\w\d\s' }}]+/gm, ''); // Remove everything that isn't a dash, letter, number or whitespace
            formatted_url = formatted_url.replaceAll(/[-\s]+/gm, '-'); // Replace dashes and whitespace with dashes
            return formatted_url;
        }
    }"
    class=""
>
    {{ html()->text($field_name . '[url]')->attributes(['data-url-field' => '', 'class' => 'input', 'id' => $field_name, 'x-model.fill' => 'url', '@input.debounce' => 'url = formattedUrl()', '@change' => 'url = $el.value; url = formattedUrl()']) }}
    <a data-url-parts="{{ $url_exists }}" class="max-w-full inline-flex overflow-hidden justify-end whitespace-nowrap mt-2 text-xs {{ $url_exists ? 'text-primary-medium' : '' }}" target="_blank">
        <span data-part>{{ $field_details['base_url'] . '/' }}</span>
        @if( isset($field_details['prefix']) && isset($field_details['prefix']['base_prefix']) )
            @if( !isset($field_details['prefix']) || !isset($field_details['prefix']['prefix_class']) )
                {{ html()->hidden($field_name . '[prefix]', $field_details['prefix']['base_prefix']) }}
            @endif
            <span data-part data-base-prefix>{{ $field_details['prefix']['base_prefix'] . '/' }}</span>
        @endif
        @if( isset($field_details['prefix']) && isset($field_details['prefix']['prefix_class']) )
            @php 
                $prefix_path = data_get($Item, $field_name . '.prefix_path');
                $prefix_output = $prefix_path;
                if (isset($field_details['prefix']['base_prefix'])) {
                    $prefix_output = ltrim($prefix_path, $field_details['prefix']['base_prefix'] . '/');
                    if (stripos($prefix_path, $field_details['prefix']['base_prefix']) === 0) {
                        $prefix_output = ltrim(substr($prefix_path, strlen($field_details['prefix']['base_prefix'])), '/');
                    }
                }
                if (!empty($prefix_path)) {
                    $prefix_path = rtrim($prefix_path, '/');
                }
            @endphp
            {{ html()->hidden($field_name . '[prefix]', $prefix_path)->attribute('data-hidden-prefix-field', '') }}
            <span data-part
                data-prefix-label
                data-prefix-field="{{ $field_details['prefix']['relation'] }}"
                data-prefix-class="{{ $field_details['prefix']['prefix_class'] }}"
                data-language="{{ data_get($Item, 'language_id', 1) }}"
            >{{ !empty($prefix_output) ? $prefix_output . '/' : '' }}</span>
        @endif
        <span data-part x-text="url"></span>
    </a>
    <div class="flex items-center mt-2">
        <button
            @click.prevent="expanded = !expanded"
            class="flex items-center justify-between p-2 w-full font-medium text-left border border-gray-200 text-gray-900 bg-white cursor-pointer hover:bg-gray-100"
            :class="{ 'border-b-0': expanded }"
        >
            <span class="mr-2">SEO settings</span>
            <svg
                class="size-6 shrink-0"
                :class="{ 'rotate-180': expanded }"
                fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
            ><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        </button>
    </div>
    <div 
        x-show="expanded"
        class="p-4 border bg-white border-gray-200"
    >
        {{-- Meta preview --}}
        @php
            $prefix_parts = [];
            if( isset($field_details['prefix']) && isset($field_details['prefix']['base_prefix']) ) {
                $prefix_parts = explode('/', $field_details['prefix']['base_prefix']);
            }
            elseif( isset($field_details['prefix']) && isset($field_details['prefix']['prefix_class']) ) {
                $prefix_parts = explode('/', data_get($Item, $field_name . '.prefix_path'));
            }
        @endphp
        <div class="seo-meta-preview mb-4">
            <div class="favicon-site-url">
                <div class="favicon"><img src="{{ Vite::asset('resources/images/favicons/favicon-96x96.png') }}" alt="Favicon" width="96" height="96" loading="lazy" /></div>
                <div class="site-url w-full truncate">
                    <div class="site">{{ config('app.name') }}</div>
                    <div class="url">
                        <span>{{ route('theme') }}</span>
                        <span>&#8250;</span>
                        <span data-seo-prefix class="prefix">
                            @foreach( array_filter($prefix_parts) as $part_slug )
                                <span>{{ $part_slug }}</span>
                                <span>&#8250;</span>
                            @endforeach
                        </span>
                        <span x-text="url"></span>
                    </div>
                </div>
            </div>
             <div class="meta-title truncate">
                <span x-text="meta_title"></span>
                @if( config('app.meta_titles.include_site_name') )
                <span>| {{ config('app.name') }}</span>
                @endif
            </div>
            <div 
                x-text="meta_description"
                class="meta-description"
            ></div>
        </div>

        {{-- Meta inputs --}}
        <div class="divide-y divide-gray-200 space-y-3">
            <div class="field-wrap">
                <label for="{{ $field_name }}-meta-title" class="label">Meta title</label>
                <div class="input-wrap">
                    {{ html()->text($field_name . '[meta_title]')->attributes(['data-meta-field' => 'title', 'class' => 'input', 'id' => $field_name . '-meta-title', 'x-model.fill' => 'meta_title', '@change' => 'meta_title = $el.value;']) }}
                </div>
            </div>
            <div class="field-wrap">
                <label for="{{ $field_name }}-meta-description" class="label">Meta description</label>
                <div class="input-wrap mb-4">
                    {{ html()->textarea($field_name . '[meta_description]')->attributes(['data-meta-field' => 'desc', 'class' => 'input', 'id' => $field_name . '-meta-description', 'x-model.fill' => 'meta_description', '@change' => 'meta_description = $el.value;', 'rows' => 3]) }}
                </div>
                {{ html()->hidden($field_name . '[no_index]', 0) }}
                <label class="label">
                    {{ html()->checkbox($field_name . '[no_index]', null, 1)->class('mr-2') }}
                    <span class="font-bold">Noindex</span> - exclude page from search engine results
                </label>
            </div>
        </div>
    </div>
    @if( $is_super_admin )
        <div 
            x-show="system_page_expanded"
            class="mt-4 p-4 bg-white border border-gray-200"
        >
            <label for="{{ $field_name }}[system_page]" class="label">System page settings</label>
            <div class="help-text">Toggle on/off with the switch & set the controller & method route in the text input</div>
            <div class="input-wrap">
                <div class="flex items-center">
                    @include('crud.fields.form.boolean', ['field_name' => $field_name . '[system_page]'])
                    {{ html()->text($field_name . '[route]')->attributes(['class' => 'input ml-4', 'placeholder' => 'controller=XX&method=YY']) }}
                </div>
            </div>
        </div>
    @else
        {{ html()->hidden($field_name . '[system_page]') }}
        {{ html()->hidden($field_name . '[route]') }}
    @endif
</div>