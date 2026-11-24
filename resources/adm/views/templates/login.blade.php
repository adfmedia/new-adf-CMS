<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    @include('includes.structure.meta')
    <body class="h-full flex flex-col relative isolate bg-[#0a0a0a]">
        @include('includes.structure.login-background')
        @yield('content')
    </body>
    @stack('footer_scripts')
</html>