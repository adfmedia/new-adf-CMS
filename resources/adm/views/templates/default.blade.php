<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    @include('includes.structure.meta')
    <body class="h-full">
        <div class="bg-gray-100 min-h-full">
            @include('includes.structure.sidebar-nav')
            <div class="nav-offset flex flex-1 flex-col">{{-- Padding offsets sidebar width --}}
                {{-- Breadcrumbs / Account --}}
                @include('includes.structure.breadcrumbs-account')
                {{-- Main col --}}
                <main class="flex-1 p-4 pb-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
    @include('includes.structure.footer-scripts')
</html>