<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Walini') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        {{-- If Inertia didn't include a page component (eg. early error), avoid asking Vite for an "undefined" page file. --}}
        @if(isset($page['component']) && $page['component'])
            @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @else
            @vite('resources/js/app.jsx')
        @endif
        @inertiaHead
        <!-- Hapus link manual ke /resources/css/app.css, sudah di-handle oleh Vite -->
    </head>
    <body class="min-h-screen font-sans antialiased" style="background: var(--bg); color: var(--text);">
        <div class="min-h-screen">
            <!-- Page Content -->
            <main>
                {{-- Inertia root container for first page visit --}}
                @inertia
            </main>
        </div>
    </body>
</html>
