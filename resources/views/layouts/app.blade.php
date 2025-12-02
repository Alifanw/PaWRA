<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Prevent FOUC: set theme before CSS loads (only data-theme, no .dark class) -->
        <script>
          (function(){
            try{
              const t = localStorage.getItem('absensiDarkMode');
              if(t === '1') {
                document.documentElement.setAttribute('data-theme','dark');
              } else if (t === '0') {
                document.documentElement.setAttribute('data-theme','light');
              } else {
                const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', prefers);
              }
            }catch(e){}
          })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans antialiased" style="background: var(--bg); color: var(--text);">
      <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
          <header class="header shadow" style="background: var(--header); color: var(--text);">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
              {{ $header }}
            </div>
          </header>
        @endisset

        <!-- Page Content -->
        <main>
          @inertia
        </main>
      </div>
    </body>
</html>
