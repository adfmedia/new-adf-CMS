@extends('templates.default')

@php
    $status_classes = [
        'enabled'       => 'bg-green-50 text-green-700 ring-green-600/20',
        'disabled'      => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
        'uninstalled'   => 'bg-gray-50 text-gray-600 ring-gray-500/10',
        'not installed' => 'bg-gray-50 text-gray-600 ring-gray-500/10',
    ];
@endphp

@section('content')
    {{-- Title bar --}}
    <div class="sm:flex sm:items-center mb-8">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{ $title }}</h1>
            <p class="mt-2 text-sm text-gray-700">{{ $description }}</p>
        </div>
    </div>

    {{-- Upload --}}
    <div class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg mb-8">
        <form action="{{ route('adm.route', ['modules', 'upload']) }}" method="POST" enctype="multipart/form-data" class="p-4 space-y-3">
            @csrf
            <div class="field-wrap">
                <label for="module_zip" class="label">Upload a module</label>
                <div class="help-text">
                    Choose a module zip, made with <code>php artisan cms:module:package</code>. A newer version of an installed module upgrades it.
                    Largest upload here: {{ number_format($max_upload / 1024 / 1024, 0) }}MB.
                </div>
                @if( !$zip_available )
                    <p class="mt-2 text-sm text-red-600">The PHP zip extension is turned off on this server, so module zips can't be installed yet.</p>
                @endif
                <div class="input-wrap flex flex-wrap items-center gap-4 mt-2">
                    <input type="file" name="module_zip" id="module_zip" accept=".zip,application/zip" required class="input px-2 max-w-md" @disabled(!$zip_available)>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="allow_reinstall" value="1" class="size-4 rounded border-gray-300">
                        Reinstall if this version is already installed
                    </label>
                    <button type="submit" class="btn btn--save" @disabled(!$zip_available)>Upload and install</button>
                </div>
                @if( $errors->has('module_zip') )
                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('module_zip') }}</p>
                @endif
            </div>
        </form>
    </div>

    {{-- Module list --}}
    <div class="flow-root mb-8">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle px-px">
                <div class="overflow-hidden shadow ring-1 ring-black/5 sm:rounded-lg">
                    <table class="listing-table min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="pl-4 sm:pl-6 pr-3 py-3.5 text-left text-sm font-semibold text-gray-900">Module</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Version</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach( $rows as $row )
                                @php
                                    $Record = $row['record'];
                                    $status_key = \Illuminate\Support\Str::before($row['status'], ' (');
                                    $files_missing = str_contains($row['status'], 'files missing');
                                @endphp
                                <tr class="{{ $loop->even ? 'bg-gray-50' : '' }} align-top">
                                    <td class="pl-4 sm:pl-6 pr-3 py-4 text-sm">
                                        <div class="font-medium text-gray-900">
                                            {{ $row['name'] }}
                                            @if( $row['core'] )
                                                <span class="ml-1 inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Core</span>
                                            @endif
                                        </div>
                                        @if( $Record && !empty($Record->manifest['description']) )
                                            <div class="text-gray-500 mt-1">{{ $Record->manifest['description'] }}</div>
                                        @endif
                                        @if( $row['error'] )
                                            <div class="text-red-600 mt-1">Last error: {{ \Illuminate\Support\Str::limit($row['error'], 200) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-700 whitespace-nowrap">
                                        {{ $row['installed'] ?? $row['version'] ?? '-' }}
                                        @if( $row['installed'] && $row['version'] && version_compare($row['version'], $row['installed'], '>') )
                                            <div class="text-xs text-yellow-700">Files are {{ $row['version'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $status_classes[$status_key] ?? $status_classes['not installed'] }}">
                                            {{ ucfirst($row['status']) }}
                                        </span>
                                    </td>
                                    <td class="py-4 pl-3 pr-4 sm:pr-6 text-sm text-right">
                                        @if( $row['core'] || $files_missing )
                                            {{-- Core modules can only be upgraded by upload --}}
                                        @elseif( !$Record || $status_key === 'uninstalled' )
                                            <form action="{{ route('adm.route', ['modules', 'install']) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="name" value="{{ $row['name'] }}">
                                                <button type="submit" class="btn btn--save">Install</button>
                                            </form>
                                        @elseif( $status_key === 'enabled' )
                                            <div class="flex flex-col items-end gap-2">
                                                <form action="{{ route('adm.route', ['modules', 'disable', $Record->id]) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn--grey">Disable</button>
                                                </form>
                                                @include('modules.partials.uninstall', ['Record' => $Record])
                                            </div>
                                        @elseif( $status_key === 'disabled' )
                                            <div class="flex flex-col items-end gap-2">
                                                <form action="{{ route('adm.route', ['modules', 'enable', $Record->id]) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn--save">Enable</button>
                                                </form>
                                                @include('modules.partials.uninstall', ['Record' => $Record])
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent activity --}}
    @if( $activity->isNotEmpty() )
        <div class="bg-white shadow ring-1 ring-black/5 sm:rounded-lg p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Recent module changes</h2>
            <ul class="divide-y divide-gray-200 text-sm">
                @foreach( $activity as $item )
                    <li class="py-2 flex flex-wrap justify-between gap-2">
                        <span><span class="font-medium text-gray-900">{{ $item['module'] }}</span>: {{ $item['action'] }}</span>
                        <span class="text-gray-500">{{ $item['user'] }}, {{ $item['date'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
