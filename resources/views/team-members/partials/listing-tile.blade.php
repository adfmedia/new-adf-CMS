<div class="shadow-xl rounded-2xl relative group transition-all hover:shadow-2xl">
    @if( !empty($TeamMember->image) && !empty($TeamMember->image['listing']) )
        <img
            src="{{ $TeamMember->image['listing']['src'] }}"
            alt="{{ $TeamMember->image['listing']['alt'] }}"
            width="{{ $TeamMember->image['listing']['width'] }}"
            height="{{ $TeamMember->image['listing']['height'] }}"
            loading="lazy"
            class="aspect-[3/2] w-full rounded-t-2xl object-cover transition-opacity group-hover:opacity-75"
        >
    @endif
    <a href="{{ $TeamMember->url }}" class="absolute inset-0"></a>
    <div class="p-4">
        <h3 class="text-lg font-semibold leading-8 text-gray-900 transition-colors group-hover:text-primary-600">{{ $TeamMember->title }}</h3>
        @if( !empty($TeamMember->job_title) )
            <p class="text-base leading-7 text-gray-600">{{ $TeamMember->job_title }}</p>
        @endif
        @if( !empty($TeamMember->email) || !empty($TeamMember->telephone) || !empty($TeamMember->social_links) )
            <ul role="list" class="mt-6 flex gap-x-6 relative">
                @if( !empty($TeamMember->email) )
                    <li>
                        <a href="mailto:{{ $TeamMember->email }}" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Email</span>
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                                <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
                            </svg>
                        </a>
                    </li>
                @endif
                @if( !empty($TeamMember->telephone) )
                    <li>
                        <a href="tel:{{ $TeamMember->telephone }}" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Telephone</span>
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </li>
                @endif
                @if( !empty($TeamMember->social_links) )
                    @foreach( $TeamMember->social_links as $link )
                        @continue( empty($link['platform']) || empty($link['link']))

                        <li>
                            <a href="{{ $link['link'] }}" class="text-gray-400 hover:text-gray-500">
                                @switch( $link['platform'] )
                                    @case('linkedin')
                                        <span class="sr-only">LinkedIn</span>
                                        <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd" />
                                        </svg>
                                        @break
                                    @case('x')
                                        <span class="sr-only">X / Twitter</span>
                                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/>
                                        </svg>
                                        @break
                                    @case('facebook')
                                        <span class="sr-only">Facebook</span>
                                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        @break
                                @endswitch
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
        @endif
    </div>
</div>