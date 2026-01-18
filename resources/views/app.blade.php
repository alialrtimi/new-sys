<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    {{-- <link rel="preconnect" href="https://fonts.bunny.net"> --}}
    {{-- <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> --}}

    <link rel="stylesheet" href="/fonts/cairo.css">
    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
    <style>
        /* @font-face مباشر داخل الصفحة (اختبار سريع) */
        @font-face {
            font-family: "CairoTestInline";
            src: url("/fonts/Cairo-Regular-3.woff2") format("woff2");
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }

        /* فرض التطبيق على الـ body بقوة */
    </style>
</head>

<body class="font-sans" style="font-family: 'CairoCustom', sans-serif !important;">
    @inertia
</body>

</html>
