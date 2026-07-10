<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PRWS - Production Readiness Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full mx-auto p-8">
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-3xl font-bold text-gray-900">PRWS</h1>
            <a href="{{ route('audits.index') }}" class="text-sm text-indigo-600 hover:underline">History</a>
    </div>
        <p class="text-gray-500 mb-8">Production Readiness Website Scanner helps you check the basics before you ship.</p>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('audits.store') }}" method="POST" class="flex gap-2">
            @csrf
            <input
                type="url"
                name="url"
                placeholder="https://yourwebsite.com"
                value="{{ old('url') }}"
                required
                class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            <button
                type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-lg transition"
            >
                Scan
            </button>
        </form>

        <p class="text-xs text-gray-400 mt-6">
            Checks 13 basic production-readiness rules: legal pages, contact info, SEO basics, reliability, and accessibility.
        </p>
    </div>
</body>
</html>