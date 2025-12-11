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
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @if(app()->isLocal())
            @viteReactRefresh
        @endif
        {{-- If Inertia didn't include a page component (eg. early error), avoid asking Vite for an "undefined" page file. --}}
        @if(app()->isLocal())
            @if(isset($page['component']) && $page['component'])
                @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
            @else
                @vite('resources/js/app.jsx')
            @endif
        @else
            @php
                $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
                $appAssets = $manifest['resources/js/app.jsx'] ?? [];
            @endphp
            @if(isset($appAssets['file']))
                <script type="module" src="{{ asset('build/' . $appAssets['file']) }}"></script>
            @endif
            @if(isset($appAssets['css']) && is_array($appAssets['css']))
                @foreach($appAssets['css'] as $css)
                    <link rel="stylesheet" href="{{ asset('build/' . $css) }}" />
                @endforeach
            @endif
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
