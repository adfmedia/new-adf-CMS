<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=1" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if( config('csp.use_meta_tag') )
        @cspMetaTag
    @endif

    @if( isset($UrlObject) )
        <title>{{ $UrlObject->meta_title . (config('app.meta_titles.include_site_name') ? ' | ' . config('app.name') : '') }}</title>
        <meta name="description" content="{{ $UrlObject->meta_description }}">
        @if( $UrlObject->no_index || \App\Helpers\RouteHelper::isPreviewRoute() || config('app.env') != 'production' )
            <meta name="robots" content="noindex, nofollow">
        @endif

        @if( isset($rel_links) && !empty($rel_links) )
            @foreach( $rel_links as $type => $href )
                <link rel="{{ $type }}" href="{{ $href }}">
            @endforeach
        @else
            <link rel="canonical" href="{{ route('theme', $UrlObject->url) }}">
        @endif
        @foreach( $UrlObject->translation_alternate_hrefs as $locale => $href )
            <link rel="alternate" href="{{ $href }}" hreflang="{{ $locale }}">
        @endforeach
    @else
        <title>{{ config('app.name') }}</title>
        <meta name="robots" content="noindex, nofollow">
    @endif

    @php
        if (!isset($UrlObject)) { $UrlObject = null; }
        if (!isset($Page)) { $Page = isset($UrlObject) && $UrlObject->urlable ? $UrlObject->urlable : null; }
    @endphp
    {{ \App\Helpers\MetaHelper::outputSocialMeta($UrlObject, $Page) }}

    @fonts(['figtree'])
    @vite('resources/css/app.css')

    @stack('preloads')

    <link rel="icon" type="image/png" sizes="96x96" href="{{ Vite::asset('resources/images/favicons/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ Vite::asset('resources/images/favicons/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ Vite::asset('resources/images/favicons/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ Vite::asset('resources/images/favicons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ route('favicon.manifest') }}">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
</head>
<body>