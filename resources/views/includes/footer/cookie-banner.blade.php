@if( config('app.cookie_banner.enabled') )
@php
    $PrivacyPage = \App\Models\Url::getSystemPage('page', 'privacy_policy');
@endphp
<div
    data-cookie-banner
    data-default-state="{{ config('app.cookie_banner.accept_by_default') ? 'true' : '' }}"
    class="fixed left-0 bottom-0 w-full z-50 transition-all translate-y-full"
>
    <div class="bg-gray-800 text-white py-4">
        <div class="container flex flex-wrap items-center">
            <div class="w-full md:w-3/4 mb-6 md:mb-0 md:pr-8">
                <p class="">
                    This site uses cookies to monitor site performance and provide a mode responsive and personalised experience.
                    You must agree to our use of certain cookies. 
                    For more information on how we use and manage cookies, please read our 
                    @if( $PrivacyPage )
                        <a 
                            href="{{ $PrivacyPage->url }}"
                            class="underline hover:no-underline"
                        >Privacy Policy</a>.
                    @else
                        Privacy Policy.
                    @endif
                </p>
            </div>
            <div class="w-full md:w-1/4 flex items-center justify-center md:justify-end space-x-6">
                @if(config('app.cookie_banner.include_reject'))
                    <a data-reject-btn href="#" class="text-sm underline hover:no-underline">Reject</a>
                @else
                    <a data-close-btn href="#" class="text-sm underline hover:no-underline">Close</a>
                @endif
                <a data-accept-btn href="#" class="btn btn--secondary">Accept all &amp; close</a>
            </div>
        </div>
    </div>
</div>
<script @cspNonce>
const COOKIE_BANNER_CNAME_CLOSED = "{{ config('app.cookie_banner.cookie_name_closed') }}"
const COOKIE_BANNER_CNAME_ACCEPTED = "{{ config('app.cookie_banner.cookie_name_accepted') }}"
const COOKIE_BANNER_CNAME_REJECTED = "{{ config('app.cookie_banner.cookie_name_rejected') }}"
const COOKIE_BANNER_CNAME_STATUS = "{{ config('app.cookie_banner.cookie_name_status') }}"
</script>
@vite('resources/js/cookie-banner.js')
@endif