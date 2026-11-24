@extends('templates.default')

@section('content')

    <div 
        data-menu-builder 
        data-menu="{{ $Menu->menu_id }}"
        data-nesting="{{ $Menu->nest_limit }}"
        class="flex"
    >
        <div class="w-1/5">
            <h3 class="text-lg font-semibold mb-4">Links</h3>

            @foreach( $link_types as $class => $type)
                <div
                    data-menu-link-type="{{ $class }}"
                    x-data="{ expanded: false }"
                    class="mb-2"
                >
                    <div>
                        <button
                            @click.prevent="expanded = !expanded"
                            class="flex items-center justify-between p-2 w-full font-medium text-left border border-gray-200 text-gray-900 bg-white hover:bg-gray-100"
                            :class="{ 'border-b-0': expanded }"
                        >
                            <span class="mr-2">{{ $type }}</span>
                            <svg
                                class="size-6 shrink-0"
                                :class="{ 'rotate-180': expanded }"
                                fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
                            ><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                    <div
                        x-show="expanded"
                        class="p-4 pb-2 border border-gray-200 max-h-80 overflow-y-auto"
                    >
                        <div data-inner-links></div>
                    </div>
                </div>
            @endforeach

            <form
                action="javascript:void(0)"
                data-external-link-form
                x-data="{ expanded: false }"
                class="mb-2"
            >
                <div>
                    <button
                        @click.prevent="expanded = !expanded"
                        class="flex items-center justify-between p-2 w-full font-medium text-left border border-gray-200 text-gray-900 bg-white hover:bg-gray-100"
                        :class="{ 'border-b-0': expanded }"
                    >
                        <span class="mr-2">External link</span>
                        <svg
                            class="size-6 shrink-0"
                            :class="{ 'rotate-180': expanded }"
                            fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
                        ><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                <div
                    x-show="expanded"
                    class="px-4 pb-2 bg-white border border-gray-200 divide-y divide-gray-200 space-y-3"
                >
                    <div class="field-wrap pt-3">
                        <label for="external-value" class="label">Link URL</label>
                        <div class="help-text">Please enter the URL to link to, including https://</div>
                        <div class="input-wrap">
                            <input  type="text" name="value" id="external-value" class="input" required />
                        </div>
                    </div>
                    <div class="field-wrap">
                        <label for="external-title" class="label">Link title</label>
                        <div class="help-text">Please enter the text to display on the link</div>
                        <div class="input-wrap">
                            <input  type="text" name="title" id="external-title" class="input" required />
                        </div>
                    </div>
                    <div class="pt-2 flex justify-end">
                        <a 
                            data-add-external
                            href="#"
                            class="btn"
                        >Add link</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="w-4/5 pl-4">
            <form action="{{ $form_action }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold mb-4 pl-4">Structure</h3>
                    <div class="flex items-center">
                        @if( $super_admin )
                            <span class="text-sm mr-2">Reference: {{ $Menu->code_reference }}</span>
                            <a data-edit-menu href="#" class="btn mr-8">Edit menu</a>
                        @endif
                        <button type="submit" class="btn btn--save">Save menu</button>
                    </div>
                </div>

                <div class="relative p-4 pt-0 mb-8">
                    <div data-structure-loader class="hidden absolute inset-0 flex items-center justify-center bg-white/75">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 animate-spin">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </div>
                    <div 
                        data-structure
                        class="menu-structure"
                    >
                        @foreach( $Menu->structure as $idx => $link )
                            @include(
                                'menu.partials.structure-item',
                                [
                                    'unique_id'     => uniqid(),
                                    'type'          => $link['type'],
                                    'value'         => $link['value'],
                                    'depth'         => $link['depth'],
                                    'prefix'        => 'links[' . $idx . ']',
                                    'title'         => $link['title'],
                                    'link'          => $link['link'],
                                    'new_tab'       => $link['new_tab'],
                                    'nofollow'      => $link['nofollow'],
                                    'include_inner' => $link['depth'] < $Menu->nest_limit,
                                    'children'      => isset($link['children']) ? $link['children'] : []
                                ]
                            )
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('menu.partials.modals.remove-item')
    @if( $super_admin )
        @include('menu.partials.modals.edit-menu', ['Menu' => $Menu, 'fields' => $config_fields])
    @endif

@endsection

@push('footer_scripts')
@vite('resources/adm/js/menu-builder.js')
@endpush