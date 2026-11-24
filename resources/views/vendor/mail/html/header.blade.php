@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
    <img 
        src="{{ Vite::asset('resources/images/email/email-header-logo.png') }}" 
        alt="{{ config('app.theme_name') }} logo" 
        width="334" height="120"
        class="logo"
        border="0"
    />
</a>
</td>
</tr>
