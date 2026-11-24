@extends('templates.default')

@section('content')
    {{-- Action / summary bar --}}
    <div class="sm:flex sm:items-center mb-8">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Form submissions - {{ $Form->title }}</h1>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <div class="flex items-center justify-end space-x-2">
                <form
                    data-delete-check-form
                    action="{{ $delete_url }}" method="POST"
                    class="items-center mr-4 hidden"
                >
                    {{ csrf_field() }}
                    <a
                        data-submit-btn
                        href="#" class="btn btn--red"
                    >Delete selected</a>
                    @include('crud.includes.delete-confirm-modal', ['title' => 'Form submissions'])
                </form>
                <a
                    href="{{ $export_url }}" target="_blank"
                    class="btn"
                >Export</a>
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
                                @foreach( $listing_columns as $column_label )
                                    <th
                                        scope="col"
                                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                    >{{ $column_label }}</td>
                                @endforeach
                                <th
                                    scope="col"
                                    class="{{ empty($listing_columns) ? 'pl-4 sm:pl-6 pr-3' : 'px-3' }} py-3.5 text-left text-sm font-semibold text-gray-900"
                                >Submitted on</th>
                                <th 
                                    scope="col"
                                    class="relative py-3.5 pl-3 pr-4 sm:pr-6"
                                ><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @if( $Submissions->isNotEmpty() )
                                @foreach( $Submissions as $Submission )
                                    @php
                                        $listing_fields = [];
                                        if (!empty($listing_columns)) {
                                            $listing_field_ids = array_keys($listing_columns);
                                            $listing_fields = $Submission->fields()
                                                ->whereIn('form_field_id', $listing_field_ids)
                                                ->pluck('value', 'form_field_id')
                                                ->toArray();
                                        }
                                    @endphp
                                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                                        <td class="w-4 pr-3 pl-4 sm:pl-6 whitespace-nowrap py-2 text-sm font-medium text-gray-900">
                                            <input
                                                data-delete-check
                                                type="checkbox" name="delete_items[]"
                                                value="{{ $Submission->id }}"
                                                class="size-4 rounded border-gray-300 text-primary-medium focus:ring-primary-medium"
                                            >
                                        </td>
                                        @foreach( $listing_columns as $field_id => $column_label )
                                            <td class="px-3 whitespace-nowrap py-2 text-sm font-medium text-gray-900">
                                                {{ isset($listing_fields[$field_id]) ? $listing_fields[$field_id] : '' }}
                                            </td>
                                        @endforeach
                                        <td class="{{ empty($listing_columns) ? 'pr-3 pl-4 sm:pl-6' : 'px-3' }} whitespace-nowrap py-2 text-sm font-medium text-gray-900">{{ $Submission->created_at->format('jS F Y, g:ia') }}</td>
                                        <td class="relative whitespace-nowrap py-2 pl-3 pr-4 text-sm font-medium sm:pr-6">
                                            <div class="flex items-center justify-end">
                                                <a
                                                    href="{{ route('adm.route', ['form-submissions', $Form->id, $Submission->id]) }}"
                                                    class="btn"
                                                >View details</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td 
                                        colspan="99"
                                        class="whitespace-nowrap px-3 sm:px-6 py-4 text-sm text-gray-500"
                                    >This form has not yet been submitted.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{ $Submissions->links() }}

@endsection