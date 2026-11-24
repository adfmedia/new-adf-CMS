@php
use Adm\Library\SidebarNavigation;
$navigation = SidebarNavigation::build();
@endphp
<div class="sidebar-nav">
    <label for="sidebar-nav-toggle" class="nav-toggle">
        <input type="checkbox" class="nav-toggle-input" id="sidebar-nav-toggle">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="toggle-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </label>
    <div class="flex grow flex-col overflow-y-auto pt-5 pb-4 bg-gray-900">
        {{-- Logo --}}
        <div class="flex shrink-0 items-center px-4">
            <img
                class="h-16 w-auto"
                src="{{ Vite::asset('resources/adm/images/adfmedia-logo.svg') }}"
                alt="adfmedia logo"
                width="32"
                height="32"
                loading="lazy"
            >
        </div>
        {{-- Navbar --}}
        <nav class="mt-5 flex flex-1 flex-col divide-y divide-gray-900 overflow-y-auto">
            <ul role="list" class="w-full px-2 flex-1 space-y-1">
                @foreach( $navigation as $link )
                    <li x-data="{ expanded: {{ isset($link['expanded']) && $link['expanded'] ? 'true' : 'false' }} }">
                        @if( isset($link['sub']) && !empty($link['sub']) )
                            <button
                                type="button"
                                class="flex items-center justify-between w-full text-left rounded-md p-2 gap-x-3 text-sm leading-6 font-semibold cursor-pointer"
                                :class="{ 'bg-gray-50 text-gray-700': expanded, 'text-white hover:bg-gray-50 hover:text-gray-700': !expanded }"
                                x-on:click="expanded = !expanded"
                            >
                                {{ $link['title'] }}
                                <svg
                                    class="text-gray-300 size-5 shrink-0"
                                    :class="{ 'rotate-90 text-gray-400': expanded, 'text-gray-300': !expanded }"
                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                                >
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul
                                class="mt-1 pl-4"
                                :class="{ 'hidden': !expanded }"
                            >
                                @if( isset($link['include_in_sub']) && $link['include_in_sub'] )
                                    <li x-data="{ active: {{ isset($link['active']) && $link['active'] ? 'true' : 'false' }} }">
                                        <a
                                            href="{{ $link['url'] }}"
                                            class="text-white hover:underline block rounded-md py-2 pr-2 pl-2 text-sm leading-6"
                                            :class="{ 'bg-gray-600': active }"
                                        >{{ $link['title'] }}</a>
                                    </li>
                                @endif
                                @foreach( $link['sub'] as $sub_link )
                                    <li x-data="{ active: {{ isset($sub_link['active']) && $sub_link['active'] ? 'true' : 'false' }} }">
                                        <a
                                            href="{{ $sub_link['url'] }}"
                                            class="text-white hover:underline block rounded-md py-2 pr-2 pl-2 text-sm leading-6"
                                            :class="{ 'bg-gray-600': active }"
                                        >{{ $sub_link['title'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <a
                                href="{{ $link['url'] }}"
                                class="block rounded-md p-2 text-sm leading-6 font-semibold"
                                :class="{ 'bg-gray-50 text-gray-700': expanded, 'text-white hover:bg-gray-50 hover:text-gray-700': !expanded }"
                            >{{ $link['title'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</div>
