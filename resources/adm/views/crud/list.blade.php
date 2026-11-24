@php
use \Illuminate\Support\Str;
$has_delete = isset($delete_action) && $delete_action['enabled'];
@endphp

@extends('templates.default')

@section('content')
    {{-- Action / summary bar --}}
    <div class="sm:flex sm:items-center mb-8">
        <div class="sm:flex-auto">
            @if( isset($title) )
                <h1 class="text-base font-semibold leading-6 text-gray-900">{{ $title }}</h1>
            @endif
            @if( isset($description) && !empty($description) )
                <p class="mt-2 text-sm text-gray-700">{{ $description }}</p>
            @endif
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <div class="flex flex-col lg:flex-row items-start lg:items-center lg:justify-end gap-2">
                @if( isset($hide_search) && !$hide_search )
                    <form
                        data-list-search-order-form
                        action="{{ isset($search_action) ? $search_action : '#' }}" method="GET"
                        class="flex items-center mr-4"
                    >
                        <input type="hidden" name="sort" value="{{ isset($sort_field) ? $sort_field : '' }}" />
                        <input type="hidden" name="sortdir" value="{{ isset($sort_dir) ? $sort_dir : '' }}" />
                        <div class="search-field-wrap">
                            <input
                                type="search" name="search" value="{{ isset($search_term) ? $search_term : '' }}"
                                class="search-input" placeholder="{{ $search_placeholder }}"
                            />
                            <button type="submit" class="search-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="search-icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </a>
                        </div>
                    </form>
                @endif
                @if( $has_delete )
                <form
                    data-delete-check-form
                    action="{{ $delete_action['url'] }}" method="POST"
                    class="items-center mr-4 hidden"
                >
                    {{ csrf_field() }}
                    <a
                        data-submit-btn
                        href="#" class="btn btn--red"
                    >Delete selected</a>
                    @include('crud.includes.delete-confirm-modal', ['title' => $title])
                </form>
                @endif
                @if( isset($extra_actions) && !empty($extra_actions) )
                    @foreach( $extra_actions as $action )
                        @continue( 
                            isset($action['super_admin_only']) && $action['super_admin_only'] &&
                            !\Illuminate\Support\Facades\Auth::guard('adm')->user()->is_super_admin
                        )

                        <a
                            href="{{ isset($action['url']) ? $action['url'] : route('adm.route', $action['slug']) }}"
                            class="{{ isset($action['class']) ? $action['class'] : 'btn' }}"
                        >{{ $action['text'] }}</a>
                    @endforeach
                @endif
                @if( !empty($export_button) && $export_button['enabled'] )
                    <a
                        href="{{ $export_button['url'] }}" 
                        class="btn"
                    >{{ $export_button['text'] }}</a>
                @endif
                @if( !empty($add_button) && $add_button['enabled'] )
                    <a
                        href="{{ $add_button['url'] }}" 
                        class="btn"
                    >{{ $add_button['text'] }}</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="flow-root mb-8">
        <div class="-my-2 overflow-x-auto">
            <div class="inline-block min-w-full py-2 align-middle px-px">
                <div class="overflow-hidden shadow ring-1 ring-black/5 sm:rounded-lg">
                    <table class="listing-table min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                @if( $has_delete )
                                    <th
                                        scope="col"
                                        class="w-4 pl-4 sm:pl-6 pr-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                    >
                                        <input
                                            data-delete-check-all
                                            type="checkbox" name="check_all"
                                            value="all"
                                            class="size-4 rounded border-gray-300 text-primary-medium focus:ring-primary-medium"
                                        >
                                    </th>
                                @endif
                                @foreach( $columns as $column )
                                    <th
                                        scope="col"
                                        class="{{ !$has_delete && $loop->first ? 'pl-4 sm:pl-6 pr-3' : 'px-3' }} py-3.5 text-left text-sm font-semibold text-gray-900"
                                    >
                                        @if( isset($column['sortable']) && $column['sortable'] )
                                            <a
                                                href="{{ $column['sort_url'] }}"
                                                class="sortable-col{{ !empty($column['sort_dir']) ? ($column['sort_dir'] == 'asc' ? ' sorted-asc' : ' sorted-desc') : '' }}"
                                            >
                                                {{ $column['label'] }}
                                                <span class="sortable-icon"></span>
                                            </a>
                                        @else
                                            {{ $column['label'] }}
                                        @endif
                                    </th>
                                @endforeach
                                @if( 
                                    (!empty($edit_button) && $edit_button['enabled']) ||
                                    (!empty($copy_button) && $copy_button['enabled']) ||
                                    (!empty($read_button) && $read_button['enabled']) ||
                                    (!empty($translate_button) && $translate_button['enabled'])
                                )
                                    <th 
                                        scope="col"
                                        class="relative py-3.5 pl-3 pr-4 sm:pr-6"
                                    ><span class="sr-only">Actions</span></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @if( $Items->isNotEmpty() )
                                @foreach( $Items as $Item )
                                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                                        @if( $has_delete )
                                            <td class="w-4 pr-3 pl-4 sm:pl-6 whitespace-nowrap py-2 text-sm font-medium text-gray-900">
                                                @if( $Item->is_system_page && !\Auth::guard('adm')->user()->is_super_admin )
                                                    <div 
                                                        class="size-4 rounded border-gray-300 bg-gray-100 text-gray-600 opacity-75 flex items-center justify-center"
                                                        title="This {{ strtolower($title_single) }} cannot be deleted"
                                                    >
                                                        <svg class="size-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                @else
                                                    <input
                                                        data-delete-check
                                                        type="checkbox" name="delete_items[]"
                                                        value="{{ data_get($Item, $item_id) }}"
                                                        class="size-4 rounded border-gray-300 text-primary-medium focus:ring-primary-medium"
                                                    >
                                                @endif
                                            </td>
                                        @endif
                                        @foreach( $columns as $field => $column )
                                            <td class="{{ !$has_delete && $loop->first ? 'pr-3 pl-4 sm:pl-6' : 'px-3' }} whitespace-nowrap py-2 text-sm font-medium text-gray-900">
                                                @if( view()->exists('crud.fields.list.' . $column['type']) )
                                                    @include('crud.fields.list.' . $column['type'])
                                                @elseif( isset($column['edit_linked']) && $column['edit_linked'] && !empty($edit_button) && $edit_button['enabled'] )
                                                    <a
                                                        href="{{ Str::replace($edit_button['id_placeholder'], data_get($Item, $item_id), $edit_button['url_pattern']) }}"
                                                        class="text-primary-medium transition-colors hover:text-primary-dark hover:underline"
                                                        title="{{ $edit_button['text'] }}"
                                                    >{{ \Illuminate\Support\Str::limit(data_get($Item, $field), 40) }}</a>
                                                @else
                                                    {{ \Illuminate\Support\Str::limit(data_get($Item, $field), 40) }}
                                                @endif
                                            </td>
                                        @endforeach
                                        @if( 
                                            (!empty($edit_button) && $edit_button['enabled']) ||
                                            (!empty($copy_button) && $copy_button['enabled']) ||
                                            (!empty($read_button) && $read_button['enabled']) ||
                                            (!empty($translate_button) && $translate_button['enabled'])
                                        )
                                        <td class="relative whitespace-nowrap py-2 pl-3 pr-4 text-sm font-medium sm:pr-6">
                                            <div class="flex items-center justify-end">
                                                @if( !empty($translate_button) && $translate_button['enabled'] )
                                                    <div data-translate-wrap class="translate-wrap">
                                                        <a
                                                            data-translate-toggle
                                                            href="#"
                                                            class="toggle-btn"
                                                        >
                                                            <span class="toggle-text">{{ $translate_button['text'] }}</span>
                                                            <svg class="toggle-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                            </svg>
                                                        </a>
                                                        <div data-translate-options class="toggle-dropdown">
                                                            <div class="py-1">
                                                                @foreach( $translate_button['languages'] as $Language )
                                                                    @continue($Language->id == data_get($Item, 'language_id'))

                                                                    @php
                                                                        $link = Str::replace(
                                                                            [$translate_button['id_placeholder'], $translate_button['lang_placeholder'], $translate_button['to_placeholder']], 
                                                                            [data_get($Item, $item_id), data_get($Item, 'language_id'), $Language->id], 
                                                                            $translate_button['url_pattern']
                                                                        );
                                                                    @endphp
                                                                    <a
                                                                        href="{{ $link }}"
                                                                        class="dropdown-link"
                                                                    >{{ $Language->title }}</a>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if( !empty($copy_button) && $copy_button['enabled'] )
                                                    <a
                                                        href="{{ Str::replace($copy_button['id_placeholder'], data_get($Item, $item_id), $copy_button['url_pattern']) }}"
                                                        class="btn btn--grey mr-4"
                                                    >{{ $copy_button['text'] }}</a>
                                                @endif
                                                @if( !empty($edit_button) && $edit_button['enabled'] )
                                                    <a
                                                        href="{{ Str::replace($edit_button['id_placeholder'], data_get($Item, $item_id), $edit_button['url_pattern']) }}"
                                                        class="btn"
                                                    >{{ $edit_button['text'] }}</a>
                                                @endif
                                                @if( !empty($read_button) && $read_button['enabled'] )
                                                    <a
                                                        href="{{ Str::replace($read_button['id_placeholder'], data_get($Item, $item_id), $read_button['url_pattern']) }}"
                                                        class="btn"
                                                    >{{ $read_button['text'] }}</a>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td 
                                        colspan="99"
                                        class="whitespace-nowrap px-3 sm:px-6 py-4 text-sm text-gray-500"
                                    >Sorry, no {{ Str::lower($title) }} could be found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{ $Items->links() }}
@endsection