<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=1" name="viewport">
    
    <title>Website maintenance | {{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">

    @vite('resources/sass/app.scss')

    <link rel="apple-touch-icon" sizes="180x180" href="{{ Vite::asset('resources/images/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ Vite::asset('resources/images/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ Vite::asset('resources/images/favicons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ Vite::asset('resources/images/favicons/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ Vite::asset('resources/images/favicons/safari-pinned-tab.svg') }}" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
</head>
<body class="h-dvh">
    <div class="h-full bg-primary-500 text-white flex flex-col items-center justify-center text-center">
        <h1 class="text-3xl font-bold sm:text-4xl mb-4">Maintenance</h1>
        <div class="text-lg sm:text-xl">
            <p>Our website is currently undergoing maintenance.</p>
            <p>Thank you for your patience.</p>
        </div>
    </div>
</body>
</html>