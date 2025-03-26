<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'UdiaNIX - Minecraft Server') }}</title>

    <!-- OpenGraph Tags -->
    <meta property="og:title" content="{{ $page['props']['og']['title'] ?? 'UdiaNIX - Minecraft Server' }}">
    <meta property="og:type" content="{{ $page['props']['og']['type'] ?? 'website' }}">
    <meta property="og:url" content="{{ $page['props']['og']['url'] ?? url()->current() }}">
    <meta property="og:image" content="{{ $page['props']['og']['image'] ?? asset('storage/background.png') }}">
    <meta property="og:description" content="{{ $page['props']['og']['description'] ?? 'Aventure-se no melhor servidor medieval sediado em Uberlândia-MG. Construa, batalhe e desvende segredos em UdiaNIX.' }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-73GFV8M4ZY"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-73GFV8M4ZY');
    </script>
</head>

<body class="font-sans antialiased dark:bg-gray-800 bg-white">
    @inertia
</body>

</html>