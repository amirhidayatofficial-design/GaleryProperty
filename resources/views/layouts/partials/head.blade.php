<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="GalleryProperty: platform properti syariah terkurasi untuk keluarga muslim. Tanpa riba, tanpa denda, tanpa sita paksa.">
<meta name="theme-color" content="#115643">

<title>{{ isset($title) && trim($title) !== '' ? $title . ' · ' : '' }}{{ config('app.name', 'GalleryProperty') }}</title>

<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|playfair-display:600,700&display=swap" rel="stylesheet" />

<!-- Scripts -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
