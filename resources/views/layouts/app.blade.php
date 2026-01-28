<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body>
        {{ $slot }}
        <p>HOLA DESDE EL LAYOUT</p>
        <a href="{{ route('welcome') }}" class="text-blue-500" wire:navigate>Home</a>
        <a href="{{ route('about') }}" class="text-blue-500" wire:navigate>About</a>
        @livewireScripts
    </body>
</html>
