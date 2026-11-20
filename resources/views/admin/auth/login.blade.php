<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin login · {{ \App\Models\Setting::get('site_name') }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-[#fffef7] flex items-center justify-center p-4">
  <div class="w-full max-w-sm">
    <div class="text-center mb-6">
      <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-900">BD<span class="text-amber-600">Gold</span></a>
      <p class="text-sm text-gray-500 mt-1">Admin panel sign in</p>
    </div>
    <form method="POST" action="{{ route('admin.login.attempt') }}" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
      @csrf
      @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2">{{ $errors->first() }}</div>
      @endif
      <div>
        <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
      </div>
      <div>
        <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password"
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
      </div>
      <label class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" name="remember" class="rounded border-gray-300 text-amber-500"> Remember me
      </label>
      <button class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-lg py-2.5 transition">Sign in</button>
    </form>
  </div>
</body>
</html>
