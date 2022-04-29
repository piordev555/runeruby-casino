<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <title>runeruby.com - {{ $code ?? -1 }}</title>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ __('general.head.description') }}">

        <meta property="og:description" content="{{ __('general.head.description') }}" />
        <meta property="og:image" content="{{ asset('/img/logo/logo_black.svg') }}" />
        <meta property="og:image:secure_url" content="{{ asset('/img/logo/logo_black.svg') }}" />
        <meta property="og:image:type" content="image/svg+xml" />
        <meta property="og:image:width" content="295" />
        <meta property="og:image:height" content="295" />
        <meta property="og:site_name" content="runeruby.com" />

        <link rel="icon" href="{{ asset('/img/logo/logo_black.svg') }}">
        <link rel="stylesheet" href="{{ mix('/css/error.css') }}">
    </head>
    <body>
        <div class="code">
            <div>{{ $code ?? -1 }}</div>
            <div>{{ $desc ?? 'An error has occurred' }}</div>
        </div>
    </body>
</html>
