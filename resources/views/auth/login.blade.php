<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DevDocs</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="h-full flex items-center justify-center p-4 bg-radial from-slate-900 via-slate-950 to-black text-slate-100 font-sans antialiased">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-500 items-center justify-center text-white shadow-xl shadow-indigo-500/20 mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Masuk ke DevDocs</h1>
            <p class="text-xs text-slate-400 mt-1">Platform Dokumentasi Teknis Personal & Tim</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
            @if ($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">Alamat Email</label>
                    <input type="email" name="email" id="email" required autofocus value="{{ old('email', 'admin@devdocs.local') }}"
                        class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-sm text-white placeholder-slate-500 outline-none transition">
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Password</label>
                    <input type="password" name="password" id="password" required value="password"
                        class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl text-sm text-white placeholder-slate-500 outline-none transition">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-400 hover:text-slate-300">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-500/25 transition duration-200 mt-2">
                    Masuk Sekarang &rarr;
                </button>
            </form>

            <!-- Demo Hint -->
            <div class="mt-6 pt-5 border-t border-slate-800/80 text-center text-xs text-slate-400">
                <span class="inline-block px-2 py-0.5 rounded bg-slate-800 text-[11px] text-slate-300 font-mono mb-1">Akun Demo Default</span>
                <p>Email: <code class="text-indigo-400 font-mono">admin@devdocs.local</code> &bull; Password: <code class="text-indigo-400 font-mono">password</code></p>
            </div>
        </div>

    </div>

</body>
</html>
