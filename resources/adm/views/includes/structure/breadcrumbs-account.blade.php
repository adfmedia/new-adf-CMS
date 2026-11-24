@php
    $User = \Illuminate\Support\Facades\Auth::guard('adm')->user();

    $Crud = new \Adm\Crud\CrudInterface();
    $Languages = $Crud->getLanguages();
    $CurrentLang = $Crud->getLanguage();

    $CurrentRoute = \Illuminate\Support\Facades\Route::current();

    $view_site_url = route('theme');
    if ($Languages->count() > 1) {
        $view_site_url = $CurrentLang->base_url;
    }

    $CF = new \Adm\Library\Cloudflare\CFCacheInterface();
    $dev_mode = $CF->getDevelopmentMode();
@endphp
<div 
    data-breadcrumb-bar
    class="sticky top-0 z-20 flex h-16 shrink-0 border-b border-gray-200 bg-white{{ !isset($form_action) ? ' shadow-xl' : '' }}"
>
    {{-- Mobile nav toggle --}}

    {{-- Desktop bar --}}
    <div class="flex flex-1 justify-between px-4">
        {{-- Breadcrumbs --}}
        <div class="flex flex-1">
            @if( isset($breadcrumbs) && !empty($breadcrumbs) )
                <nav class="hidden lg:flex items-center space-x-1 text-sm" aria-label="Breadcrumb">
                    @foreach( $breadcrumbs as $crumb )
                        @if( !$loop->first )
                            <svg class="size-5 shrink-0 text-gray-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        @endif
                        @if( !$loop->last )
                            <a 
                                href="{{ $crumb['url'] }}"
                                class="text-gray-700 hover:text-gray-500"
                            >{{ $crumb['title'] }}</a>
                        @else
                            <span class="text-gray-400">{{ $crumb['title'] }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif
        </div>

        {{-- Quick links & Account --}}
        <div class="ml-4 md:ml-6 flex items-center space-x-3">
            {{-- Cloudflare controls --}}
            @if( $CF->isEnabled() )
                <div class="hidden lg:flex items-center space-x-3 pr-3 border-r border-gray-300 text-sm">
                    <a
                        data-cf-btn="purge"
                        href="#"
                        class="hover:underline"
                    >Purge site cache</a>
                    <a
                        data-cf-btn="dev-mode"
                        href="#"
                        class="hover:underline cf-mode-link {{ $dev_mode }}"
                    >Development mode: <span class="mode-label"></span></a>
                </div>
            @endif

            {{-- Language switch --}}
            @if( $Languages->isNotEmpty() && $Languages->count() > 1 )
                <div class="pr-3 border-r border-gray-300 text-sm">
                    <form
                        data-language-change-form
                        action="{{ route('adm.route', 'change-language')}}" method="POST"
                        class="field-wrap py-0"
                    >
                        @csrf
                        <input type="hidden" name="slug" value="{{ $CurrentRoute->parameter('section') }}" />

                        <div class="input-wrap mt-0">
                            <select name="language" class="input">
                                @foreach( $Languages as $Language )
                                    <option value="{{ $Language->id }}" @selected($Language->id == $CurrentLang->id)>{{ $Language->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Quick links --}}
            <div class="pr-3 border-r border-gray-300 text-sm">
                <a href="{{ $view_site_url }}" target="_blank" class="hover:underline">View site</a>
            </div>

            {{-- Account --}}
            <div 
                x-data="{ open: false }"
                @keydown.escape.stop="open = false;"
                @click.away="open = false;"
                class="flex items-center"
            >
                <div class="relative">
                    <button
                        type="button"
                        class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-hidden focus:ring-2 focus:ring-primary-medium focus:ring-offset-2 lg:rounded-md lg:p-2 lg:hover:bg-gray-50"
                        @click="open = !open"
                    >
                        <span class="ml-3 text-sm font-medium text-gray-700">{{ $User->first_name }}</span>
                        <svg class="ml-1 hidden size-5 shrink-0 text-gray-400 lg:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        class="absolute right-0 top-full z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-hidden"
                        style="display: none;"
                    >
                        <a href="{{ route('adm.route', ['admin-users', 'edit', $User->id]) }}" class="block px-4 py-2 text-sm text-gray-700">Your profile</a>
                        <form action="{{ route('adm.logout') }}" method="post" class="block px-4 py-2 text-sm text-gray-700">
                            @csrf
                            <button type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(session('success'))
        <div data-flash-msg class="flash-msg success">{{ session('success') }}</div>
        @if( isset($broadcast_channel) )
            <script @cspNonce>const FLASH_BROADCAST = '{{ $broadcast_channel }}';</script>
        @endif
    @elseif(session('error'))
        <div data-flash-msg class="flash-msg error">{{ session('error') }}</div>
    @endif
</div>