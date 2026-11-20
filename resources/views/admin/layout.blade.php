<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">
  <title>@yield('title', 'Dashboard') · Admin · {{ \App\Models\Setting::get('site_name') }}</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .mono { font-family: 'JetBrains Mono', monospace; }
    .nav-link { display:flex; align-items:center; gap:.65rem; padding:.55rem .75rem; border-radius:.6rem; font-size:.875rem; font-weight:500; color:#cbd5e1; }
    .nav-link:hover { background:rgba(255,255,255,.06); color:#fff; }
    .nav-link.active { background:#f59e0b; color:#1f2937; font-weight:700; }
    .nav-link svg { width:18px; height:18px; flex-shrink:0; }
    .card { background:#fff; border:1px solid #e5e7eb; border-radius:1rem; }
    .tbl { width:100%; font-size:.8125rem; }
    .tbl th { text-align:left; font-size:.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; padding:.7rem .75rem; background:#f9fafb; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
    .tbl td { padding:.65rem .75rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .tbl tbody tr:hover { background:#fafafa; }
    .inp { width:100%; border:1px solid #d1d5db; border-radius:.6rem; padding:.5rem .75rem; font-size:.875rem; background:#fff; }
    .inp:focus { outline:none; border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.2); }
    .lbl { display:block; font-size:.75rem; font-weight:600; color:#374151; margin-bottom:.3rem; }
    .btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; padding:.5rem 1rem; border-radius:.6rem; font-size:.8125rem; font-weight:600; transition:background .15s; white-space:nowrap; }
    .btn-primary { background:#f59e0b; color:#fff; } .btn-primary:hover { background:#d97706; }
    .btn-light { background:#fff; border:1px solid #d1d5db; color:#374151; } .btn-light:hover { background:#f9fafb; }
    .btn-danger { background:#fee2e2; color:#b91c1c; } .btn-danger:hover { background:#fecaca; }
    .btn-sm { padding:.3rem .65rem; font-size:.75rem; }
    .badge { display:inline-flex; align-items:center; padding:.1rem .5rem; border-radius:999px; font-size:.6875rem; font-weight:600; }
  </style>
  @stack('head')
</head>
<body class="bg-gray-100 text-gray-800 antialiased">
@php
  $nav = [
    ['admin.dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'admin.dashboard'],
    ['admin.rates.index', 'Update rates', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'admin.rates.*'],
    ['admin.history.index', 'Price history', 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z', 'admin.history.*'],
    ['admin.purities.index', 'Purities', 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'admin.purities.*'],
    ['admin.countries.index', 'Country prices', 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'admin.countries.*'],
    ['admin.faqs.index', 'FAQs', 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'admin.faqs.*'],
    ['admin.visitors.index', 'Visitors', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'admin.visitors.*'],
    ['admin.visits.index', 'Visit log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'admin.visits.*'],
    ['admin.estimates.index', 'Saved estimates', 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'admin.estimates.*'],
    ['admin.users.index', 'Admin users', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'admin.users.*'],
    ['admin.settings.edit', 'Settings', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z', 'admin.settings.*'],
  ];
@endphp

<div id="backdrop" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"></div>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-white flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-200">
  <div class="h-16 flex items-center justify-between px-5 border-b border-white/10">
    <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold">BD<span class="text-amber-400">Gold</span> <span class="text-xs font-medium text-gray-400">admin</span></a>
    <button type="button" id="closeSidebar" class="lg:hidden text-gray-400 hover:text-white" aria-label="Close menu">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>
  <nav class="flex-1 overflow-y-auto p-3 space-y-1">
    @foreach($nav as [$route, $label, $icon, $pattern])
      <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
        {{ $label }}
      </a>
    @endforeach
  </nav>
  <div class="p-3 border-t border-white/10 space-y-1">
    <a href="{{ route('home') }}" target="_blank" class="nav-link">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      View website
    </a>
    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button class="nav-link w-full">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Log out
      </button>
    </form>
  </div>
</aside>

<div class="lg:pl-64 min-h-screen flex flex-col">
  <header class="h-14 sm:h-16 bg-white border-b border-gray-200 sticky top-0 z-20 flex items-center justify-between px-4 sm:px-6 gap-3">
    <div class="flex items-center gap-3 min-w-0">
      <button type="button" id="openSidebar" class="lg:hidden text-gray-600 hover:text-gray-900" aria-label="Open menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <h1 class="text-base sm:text-lg font-bold text-gray-900 truncate">@yield('title', 'Dashboard')</h1>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      @yield('actions')
      <span class="hidden sm:inline text-xs text-gray-500 ml-2">{{ auth()->user()->name }}</span>
    </div>
  </header>

  <main class="flex-1 p-4 sm:p-6 space-y-5">
    @if(session('success'))
      <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">
        <ul class="list-disc pl-4 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    @yield('content')
  </main>
</div>

<script>
  (function () {
    const sb = document.getElementById('sidebar'), bd = document.getElementById('backdrop');
    const open = () => { sb.classList.remove('-translate-x-full'); bd.classList.remove('hidden'); };
    const close = () => { sb.classList.add('-translate-x-full'); bd.classList.add('hidden'); };
    document.getElementById('openSidebar').addEventListener('click', open);
    document.getElementById('closeSidebar').addEventListener('click', close);
    bd.addEventListener('click', close);
    document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
      if (!confirm(f.dataset.confirm)) e.preventDefault();
    }));
  })();
</script>
@stack('scripts')
</body>
</html>
