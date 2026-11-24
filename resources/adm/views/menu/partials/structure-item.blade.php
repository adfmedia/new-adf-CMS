<div
    data-structure-item
    data-type="{{ $type }}" 
    data-value="{{ $value }}" data-depth="{{ $depth }}"
    data-prefix="{{ $prefix }}"
    class="menu-structure-item"
>
    <input type="hidden" name="{{ $prefix . '[type]' }}" value="{{ $type }}" />
    <input type="hidden" name="{{ $prefix . '[value]' }}" value="{{ $value }}" />
    <input type="hidden" name="{{ $prefix . '[depth]' }}" value="{{ $depth }}" />

    <div data-item-link-wrap class="structure-link-wrap">
        <div class="link-header">
            <div data-handle class="handle">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="handle-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                </svg>
            </div>
            <div data-title-label class="title">{{ $title }}</div>
            @if( isset($include_inner) && $include_inner )
                <div data-collapse-toggle class="collapse-toggle" title="Expand / collapse children">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="toggle-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                    </svg>
                </div>
            @endif
            <div data-dd-toggle class="dd-toggle" title="Menu item settings">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="toggle-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </div>
        </div>
        <div class="link-options">
            <div class="field-wrap">
                <div for="{{ $unique_id . '-value' }}" class="label">Link URL</div>
                @if( $type == 'external' )
                    <div class="help-text">Please enter the URL to link to, including https://</div>
                    <div class="input-wrap">
                        <input 
                            type="text" name="{{ $prefix . '[value]' }}"
                            id="{{ $unique_id . '-value' }}"
                            value="{{ $value }}"
                            class="input"
                        />
                    </div>
                @else
                    <a href="{{ $link }}" target="_blank" class="text-sm text-primary-medium hover:underline">{{ $link }}</a>
                @endif
            </div>
            <div class="field-wrap">
                <label for="{{ $unique_id . '-title' }}" class="label">Title / label</label>
                <div class="help-text">Set the title / label to display for this link</div>
                <div class="input-wrap">
                    <input 
                        data-title-input
                        type="text" name="{{ $prefix . '[title]' }}"
                        id="{{ $unique_id . '-title' }}"
                        value="{{ $title }}"
                        class="input"
                    />
                </div>
            </div>
            <div data-advanced-wrap class="advanced-wrap">
                <div class="advanced-header">
                    <div class="text-sm py-2 pl-2">Advanced settings</div>
                    <div data-adv-dd-toggle class="advanced-dd-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="toggle-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
                <div class="advanced-inner">
                    <div class="flex items-start mb-2">
                        <div class="h-6 flex items-center">
                            <input type="hidden" name="{{ $prefix }}[new_tab]" value="0" />
                            <input
                                type="checkbox" name="{{ $prefix }}[new_tab]" id="{{ $unique_id . '-new-tab' }}" value="1"
                                @checked($new_tab)
                                class="size-4 rounded border-gray-300 text-primary-medium focus:ring-primary-medium"
                            >
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="{{ $unique_id . '-new-tab' }}" class="font-medium text-gray-900">Open link in new tab</label>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="h-6 flex items-center">
                            <input type="hidden" name="{{ $prefix }}[nofollow]" value="0" />
                            <input
                                type="checkbox" name="{{ $prefix }}[nofollow]" id="{{ $unique_id . '-nofollow' }}" value="1"
                                @checked($nofollow)
                                class="size-4 rounded border-gray-300 text-primary-medium focus:ring-primary-medium"
                            >
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="{{ $unique_id . '-nofollow' }}" class="font-medium text-gray-900">Add rel="nofollow" to link</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pt-2 flex">
                <a 
                    data-remove-item 
                    href="#"
                    class="text-sm text-red-600 hover:underline"
                >Remove</a>
            </div>
        </div>
    </div>

    @if( isset($include_inner) && $include_inner )
        <div class="link-child-wrap">
            <div 
                data-item-nest 
                data-prefix="{{ $prefix . '[children]' }}"
                class="link-children"
            >
                @if( isset($children) && !empty($children) )
                    @foreach( $children as $child_idx => $child )
                        @include(
                            'menu.partials.structure-item',
                            [
                                'unique_id'     => uniqid(),
                                'type'          => $child['type'],
                                'value'         => $child['value'],
                                'depth'         => $child['depth'],
                                'prefix'        => $prefix . '[children][' . $child_idx . ']',
                                'title'         => $child['title'],
                                'link'          => $child['link'],
                                'new_tab'       => $child['new_tab'],
                                'nofollow'      => $child['nofollow'],
                                'include_inner' => $child['depth'] < $Menu->nest_limit,
                                'children'      => isset($child['children']) ? $child['children'] : []
                            ]
                        )
                    @endforeach
                @endif
            </div>
        </div>
    @endif
</div>