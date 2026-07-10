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
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4 flex items-start gap-2">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('audits.store') }}" method="POST" class="flex gap-2" id="scan-form">
            @csrf
            <input
                type="url"
                name="url"
                id="url-input"
                placeholder="https://yourwebsite.com"
                value="{{ old('url') }}"
                required
                class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            <button
                type="submit"
                id="scan-btn"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-lg transition disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2 min-w-[110px] justify-center"
            >
                <span id="scan-btn-label">Scan</span>
            </button>
        </form>

        <p class="text-xs text-gray-400 mt-6">
            Checks 13 basic production-readiness rules: legal pages, contact info, SEO basics, reliability, and accessibility.
        </p>
    </div>
    <script>
        document.getElementById('scan-form').addEventListener('submit', function () {
            const btn = document.getElementById('scan-btn');
            const label = document.getElementById('scan-btn-label');
            const input = document.getElementById('url-input');

            btn.disabled = true;
            input.readOnly = true; // readOnly, not disabled, disabled fields don't submit their value

            label.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="ml-1">Scanning...</span>
            `;
            label.classList.add('flex', 'items-center');
        });
    </script>
</body>
</html>