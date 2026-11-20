<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', $settings['site_title'] ?? config('app.name'))</title>
  <meta name="description" content="{{ $settings['meta_description'] ?? '' }}">
  <meta name="theme-color" content="#f59e0b">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    html { scroll-behavior: smooth; scroll-padding-top: 76px; }
    body { font-family: 'Inter', sans-serif; background: #fffef7; }
    .mono { font-family: 'JetBrains Mono', monospace; letter-spacing: -0.01em; }
    .no-scrollbar { scrollbar-width: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    details summary::-webkit-details-marker { display: none; }
    details summary { list-style: none; }
    details[open] .chevron { transform: rotate(180deg); }
    .chevron { transition: transform 0.2s; }
  </style>
  @stack('head')
</head>
<body class="antialiased text-gray-800 overflow-x-hidden">
  @yield('content')
  @stack('scripts')
</body>
</html>
