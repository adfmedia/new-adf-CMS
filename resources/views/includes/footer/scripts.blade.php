<script @cspNonce>
const ALPJS = {{ config('app.alpinejs_fe_enabled') ? 'true' : 'false' }};
const COOKIE_SECURE = {{ config('session.secure') ? 'true' : 'false' }};
const URLID = {{ isset($UrlObject) && $UrlObject ? $UrlObject->id : 0 }};
const CAPTCHAKEY = {!! !empty(config('services.recaptcha.site_key')) ? '"' . config('services.recaptcha.site_key') . '"' : 'false' !!};

const LIGHTBOX_CSS = '{{ Vite::asset('resources/css/lightbox.css') }}';
const CAROUSEL_CSS = '{{ Vite::asset('resources/css/carousel.css') }}';
const CF_STATUS = {{ !empty(config('services.cloudflare.api_key')) ? 'true' : 'false' }};
</script>
@vite('resources/js/app.js')