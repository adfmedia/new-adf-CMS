<details class="text-left">
    <summary class="cursor-pointer text-sm text-red-700 hover:underline text-right">Uninstall</summary>
    <form
        action="{{ route('adm.route', ['modules', 'uninstall', $Record->id]) }}" method="POST"
        class="mt-2 p-3 rounded-md ring-1 ring-red-200 bg-red-50 space-y-2 w-72"
    >
        @csrf
        <p class="text-sm text-gray-700">Uninstalling turns {{ $Record->name }} off. Its content is kept, so installing it again brings it back.</p>
        <label class="flex items-start gap-2 text-sm text-gray-700">
            <input type="checkbox" name="remove_files" value="1" class="mt-0.5 size-4 rounded border-gray-300">
            Also remove its files (moved to modules/.backup)
        </label>
        <label class="flex items-start gap-2 text-sm text-gray-700">
            <input type="checkbox" name="delete_data" value="1" class="mt-0.5 size-4 rounded border-gray-300">
            Also delete its data. This can't be undone.
        </label>
        <div>
            <label for="confirm_name_{{ $Record->id }}" class="block text-xs text-gray-600">To delete data, type <strong>{{ $Record->name }}</strong></label>
            <input type="text" name="confirm_name" id="confirm_name_{{ $Record->id }}" class="input mt-1 w-full" autocomplete="off">
        </div>
        <button type="submit" class="btn btn--red">Uninstall {{ $Record->name }}</button>
    </form>
</details>
