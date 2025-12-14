<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Finanzas IA')</title>

    <!-- Tailwind CDN (modo simple para MVP) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- HEADER -->
    <header class="bg-white shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-indigo-600">💸 Finanzas IA</span>
            </div>

            <nav class="flex gap-4 text-sm">
                <a href="/dashboard" class="text-gray-600 hover:text-indigo-600">
                    Dashboard
                </a>
                <a href="/ai/inbox" class="text-gray-600 hover:text-indigo-600">
                    IA
                </a>
                <a href="/events" class="text-gray-600 hover:text-indigo-600">
                    Movimientos
                </a>
            </nav>
        </div>
    </header>

    <!-- CONTENIDO -->
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 py-6">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t">
        <div class="max-w-5xl mx-auto px-4 py-3 text-xs text-gray-500 flex justify-between">
            <span>© {{ date('Y') }} Finanzas IA</span>
            <span>Modo aprendizaje activo 🧠</span>
        </div>
    </footer>

</body>
</html>
