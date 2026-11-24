@php
    $FooterMenu = \App\Models\Menu::getMenu('footer_menu');

    $social_links = array_filter([
        'facebook'  => common_text('social_facebook'),
        'twitterx'   => common_text('social_twitterx'),
        'linkedin'  => common_text('social_linkedin'),
        'youtube'   => common_text('social_youtube'),
        'instagram' => common_text('social_instagram'),
    ]);

    $address = array_filter([
        common_text('address_company'),
        common_text('address_line_one'),
        common_text('address_line_two'),
        common_text('address_city'),
        common_text('address_county'),
        common_text('address_postcode'),
        common_text('address_country'),
    ]);
@endphp
    <footer class="container px-6 lg:px-8 pb-4">
        <div class="flex flex-col items-center gap-2 mb-14 md:flex-row md:justify-between">
            <a href="{{ route('theme') }}">
                <span class="sr-only">Home</span>
                <img
                    class="h-full w-full object-cover max-w-[162px]"
                    src="{{ Vite::asset('resources/images/logoipsum.svg') }}"
                    alt="Logoipsum"
                    width="352"
                    height="58"
                    loading="lazy"
                >
            </a>

            <div class="flex items-center flex-col gap-5 md:flex-row lg:gap-[30px]">
                <p class="text-heading-6 font-chivo font-bold">Ready to get started?</p>
                <a class="flex items-center z-10 relative transition-all duration-200 group px-[22px] py-[15px] lg:px-[32px] lg:py-[22px] rounded-md bg-gray-900 text-white hover:bg-gray-100 hover:text-gray-900 hover:-translate-y-[2px] w-fit" href="{{ route('theme') }}/adm"><span class="block text-inherit w-full h-full rounded-md text-lg font-chivo font-semibold">Login to CMS</span></a>
            </div>
        </div>
        <p class="text-sm text-right">developer@bespokedigital.agency | !Staff0nly!</p>
        @if( $FooterMenu && !empty($FooterMenu->structure) )
            <nav class="-mb-6 columns-2 sm:flex sm:justify-center sm:space-x-12" aria-label="Footer">
                @foreach( $FooterMenu->structure as $link )
                    <div class="pb-6">
                        <a
                            href="{{ $link['link'] }}"
                            {!! $link['target'] . $link['rel'] !!}
                            class="text-sm leading-6 text-gray-600 hover:text-gray-900"
                        >{{ $link['title'] }}</a>
                    </div>
                @endforeach
            </nav>
        @endif
        <div class="mt-10 flex justify-center space-x-10">
            @if( !empty($social_links) )
                <div class="flex space-x-4">
                    @foreach( $social_links as $platform => $url )
                        <a href="{{ $url }}" target="_blank" class="w-6 pt-1 rounded-full bg-white inline-flex items-center justify-center">
                            <span class="sr-only">{{ ucfirst($platform) }}</span>
                            @switch($platform)
                                @case('facebook')
                                    <svg class="size-6" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z" fill="currentColor"/>
                                    </svg>
                                @break
                                @case('twitterx')
                                    <svg class="size-6" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z" fill="currentColor" />
                                    </svg>
                                @break
                                @case('linkedin')
                                    <svg class="size-6" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z" fill="currentColor"></path>
                                    </svg>
                                @break
                                @case('youtube')
                                    <svg class="size-6" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill="currentColor"></path>
                                    </svg>
                                @break
                                @case('instagram')
                                    <svg class="size-6" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077" fill="currentColor"></path>
                                    </svg>
                                @break
                            @endswitch
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        @if( !empty($address) )
            <p class="mt-10 text-center text-xs leading-5 text-gray-500">{{ implode(', ', $address) }}</p>
        @endif
        <p class="mt-10 text-center text-xs leading-5 text-gray-500">&copy; {{ date("Y") }} {{ common_text('copyright') }}</p>
    </footer>

    @include('includes.footer.scripts')
    @include('includes.footer.schema')
    @include('includes.footer.cookie-banner')
</body>
</html>
