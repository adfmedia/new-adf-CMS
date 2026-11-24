@php
    $HeaderMenu = \App\Models\Menu::getMenu('header_menu');
    $SearchPage = \App\Models\Url::getSystemPage('search', 'index');
    
    $login_enabled = env('FE_LOGIN_ENABLED');
    if ($login_enabled) {
        $logged_in = Auth::user();
        $ProfilePage = \App\Models\Url::getAccountPage();
    }
@endphp
<header class="w-full bg-white shadow-md sticky top-0 left-0 py-6 z-20">
    @if( $login_enabled )
        <div class="hidden lg:block bg-gray-800 text-white -mt-6 mb-6 py-2">
            <div class="container flex items-center justify-end space-x-6 text-sm font-semibold leading-6">
                @if( $logged_in )
                    @if( $ProfilePage )
                        <a href="{{ $ProfilePage->url }}" class="hover:underline focus:underline">My account</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="hover:underline focus:underline">Log out</button>
                    </form>
                @else
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="hover:underline focus:underline">Register</a>
                    @endif
                    <a href="{{ route('login') }}" class="hover:underline focus:underline">Log in</a>
                @endif
            </div>
        </div>
    @endif
    <div class="container">
        <nav class="flex items-center justify-between">
            <a href="{{ route('theme') }}" class="w-1/2 lg:w-auto mr-4">
                <span class="sr-only">Home</span>
                <img
                    class="block w-64"
                    src="{{ Vite::asset('resources/images/logoipsum.svg') }}"
                    alt="Logoipsum"
                    width="352"
                    height="58"
                >
             </a>
            @if(
                ($HeaderMenu && !empty($HeaderMenu->structure)) ||
                $SearchPage
            )
            <div class="hidden lg:flex space-x-6 items-center">
                @if( $SearchPage )
                    <form action="{{ $SearchPage->url }}" method="GET" class="header-search-form">
                        <div class="search-field-wrap">
                            <input
                                type="search" name="search" value="{{ isset($search_term) ? $search_term : '' }}"
                                class="search-input" placeholder="Search..."
                            >
                            <button type="submit" class="search-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="search-icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                @endif
                @if( $HeaderMenu && !empty($HeaderMenu->structure) )
                    @foreach( $HeaderMenu->structure as $link )
                        @if( isset($link['children']) && !empty($link['children']) )
                            <div class="relative group">
                                <div class="flex items-center">
                                    <a
                                        href="{{ $link['link'] }}"
                                        {!! $link['target'] . $link['rel'] !!}
                                        class="text-sm font-semibold leading-6 hover:underline mr-1"
                                    >{{ $link['title'] }}</a>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 group-hover:rotate-180">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div class="hidden absolute top-full right-0 group-hover:block">
                                    <div class="bg-white px-6 py-3 mt-6 space-y-3 shadow-md rounded-md whitespace-nowrap">
                                        @foreach( $link['children'] as $child )
                                            <div>
                                                <a
                                                    href="{{ $child['link'] }}"
                                                    {!! $child['target'] . $child['rel'] !!}
                                                    class="text-sm font-semibold leading-6 hover:underline{{ !$loop->last ? ' mb-2' : '' }}"
                                                >{{ $child['title'] }}</a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <a
                                href="{{ $link['link'] }}"
                                {!! $link['target'] . $link['rel'] !!}
                                class="text-sm font-semibold leading-6 hover:underline"
                            >{{ $link['title'] }}</a>
                        @endif
                    @endforeach
                @endif
            </div>
            <div class="flex lg:hidden items-center justify-end">
                <a
                    data-mobile-nav-toggle
                    href="#"
                    class=""
                >
                    <svg 
                        data-open-icon
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                        class="size-6"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg 
                        data-close-icon
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                        class="hidden size-6"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
            @endif
        </nav>
    </div>
</header>
{{-- Mobile nav --}}
@if( ($HeaderMenu && !empty($HeaderMenu->structure)) || $SearchPage || $login_enabled )
<nav data-mobile-nav class="hidden fixed inset-0 z-10">
    <div 
        data-mobile-nav-inner data-sticky-header="header.sticky"
        class="h-full bg-gray-200 flex flex-col px-4"
    >
        <div class="flex-1">
            @if( $HeaderMenu && !empty($HeaderMenu->structure) )
                <div class="overflow-y-auto">
                    @foreach( $HeaderMenu->structure as $link )
                        @if( isset($link['children']) && !empty($link['children']) )
                            <div data-has-sub class="py-3 my-2 border-b border-gray-400">
                                <a
                                    data-sub-toggle 
                                    href="#"
                                    class="flex items-center justify-between space-x-4"
                                >
                                    <span class="font-semibold">{{ $link['title'] }}</span>
                                    <svg
                                        data-sub-icon 
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                                        class="size-6"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </a>
                                <div data-sub-nav class="hidden pt-4 pl-6">
                                    <div class="flex flex-col space-y-3">
                                        <a
                                            href="{{ $link['link'] }}"
                                            {!! $link['target'] . $link['rel'] !!}
                                            class="text-base font-medium leading-6 hover:underline"
                                        >{{ $link['title'] }}</a>
                                        @foreach( $link['children'] as $child )
                                            <a
                                                href="{{ $child['link'] }}"
                                                {!! $child['target'] . $child['rel'] !!}
                                                class="text-base font-medium leading-6 hover:underline"
                                            >{{ $child['title'] }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-3 my-2 border-b border-gray-400">
                                <a
                                    href="{{ $link['link'] }}"
                                    {!! $link['target'] . $link['rel'] !!}
                                    class="font-semibold leading-6 hover:underline"
                                >{{ $link['title'] }}</a>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        <div class="pt-4 space-y-6">
            @if( $SearchPage )
                <form action="{{ $SearchPage->url }}" method="GET" class="header-search-form">
                    <div class="search-field-wrap w-full">
                        <input
                            type="search" name="search" value="{{ isset($search_term) ? $search_term : '' }}"
                            class="search-input w-full" placeholder="Search..."
                        >
                        <button type="submit" class="search-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="search-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </button>
                    </div>
                </form>
            @endif
            @if( $login_enabled )
                <div class="pb-4 flex items-center justify-between space-x-4 font-semibold">
                    @if( $logged_in )
                        @if( $ProfilePage )
                            <a href="{{ $ProfilePage->url }}" class="py-2 hover:underline focus:underline">My account</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="py-2 hover:underline focus:underline">Log out</button>
                        </form>
                    @else
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="py-2 hover:underline focus:underline">Register</a>
                        @endif
                        <a href="{{ route('login') }}" class="py-2 hover:underline focus:underline">Log in</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</nav>
@endif
@include('includes.common.breadcrumbs')
