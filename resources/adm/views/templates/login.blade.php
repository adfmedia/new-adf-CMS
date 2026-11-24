<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    @include('includes.structure.meta')
    <body class="h-full flex flex-col">
        @yield('content')
    </body>
    @stack('footer_scripts')
</html>