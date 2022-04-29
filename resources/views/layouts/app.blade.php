<!DOCTYPE html>
<html>
    <head>
        <title>RuneRuby</title>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, height=device-height, minimum-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta property="og:image" content="{{ asset('/img/misc/promo.png') }}" />
        <meta property="og:image:secure_url" content="{{ asset('/img/misc/promo.png') }}" />
        <meta property="og:image:type" content="image/svg+xml" />
        <meta property="og:image:width" content="295" />
        <meta property="og:image:height" content="295" />
        <meta property="og:site_name" content="RuneRuby" />

        @if(env('APP_DEBUG'))
            <meta http-equiv="Expires" content="Mon, 26 Jul 1997 05:00:00 GMT">
            <meta http-equiv="Pragma" content="no-cache">
        @endif

        <link rel="icon" href="{{ asset('/favicon.png') }}">
        <link rel="manifest" href="/manifest.json">

        <script type="text/javascript">
            window.Layout = {
                Frontend: '{!! base64_encode(file_get_contents(public_path('css/app.css'))) !!}',
                Backend: '{!! base64_encode(file_get_contents(public_path('css/admin/app.css'))) !!}'
            }
        </script>

        <script>
            window.Notifications = {
                vapidPublicKey: '{{ config('webpush.vapid.public_key') }}'
            };
        </script>

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-155249704-1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'UA-155249704-1');
        </script>
    </head>
    <body>
        <div id="app">
            <layout></layout>
        </div>

        <script src="{{ asset(mix('/js/app.js')) }}"></script>

        @if(env('APP_DEBUG'))
            <script src="http://localhost:8098"></script>
        @endif
    </body>
</html>
