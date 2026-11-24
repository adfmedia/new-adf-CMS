<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CMS{{ isset($title) ? ' - ' . $title : '' }} | {{ config('app.name', 'Laravel') }}</title>
    <meta name="robots" content="noindex, nofollow">
    @fonts(['inter'])
    @vite('resources/adm/css/style.css')
</head>