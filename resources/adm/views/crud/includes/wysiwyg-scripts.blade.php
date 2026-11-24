<script @cspNonce>
const WYSIWYG_BASE_URL      = '{{ route('theme') }}';
const WYSIWYG_APP_CSS       = '{{ Vite::asset('resources/css/app.css') }}';
const WYSIWYG_BODY_CLASS    = 'p-4 prose max-w-none';
</script>
@vite('resources/adm/js/wysiwygs.js')
<div id="wysiwyg-evt-target"></div>
<style scoped @cspNonce>
.tox .tox-promotion-link, .tox-statusbar__branding { display: none!important; }
</style>