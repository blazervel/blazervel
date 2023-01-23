<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark:bg-chrome-900">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Blazervel') }}</title>
    @vite('vendor/blazervel/blazervel/resources/js/main.ts')
  </head>
  <body class="font-sans antialiased"></body>
</html>