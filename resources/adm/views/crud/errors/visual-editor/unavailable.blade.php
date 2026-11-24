<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    @include('includes.structure.meta')
    <body class="h-full">
        <div class="bg-gray-500/75 min-h-full flex items-center justify-center">
            <div class="overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl max-w-lg">
                <div>
                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-yellow-100">
                        <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 text-yellow-400">
                            <path d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" fill-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center">
                        <h3 class="text-lg font-semibold text-gray-900">Preview unavailable</h3>
                        <div class="mt-2">
                            <p class="text-gray-500">Sorry, preview is unavailable for this page - it could be because it is login restricted, or is redirected.</p>
                        </div>
                        @if( $super_admin && $e )
                            <div class="mt-4">
                                <p class="text-gray-900 font-semibold">Error thrown:</p>
                                <p class="text-gray-500">{{ $e->getMessage() }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>