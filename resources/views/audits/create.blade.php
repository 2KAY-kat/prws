<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PRWS - Production Readiness Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen py-16">

    <div class="max-w-lg w-full mx-auto px-6">
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

    {{-- Showcase grid --}}
    @if ($showcaseAudits->isNotEmpty())
        <div class="max-w-5xl mx-auto px-6 mt-20">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900">Recently Scanned</h2>
                <p class="text-sm text-gray-500">{{ number_format($totalScans) }} {{ Str::plural('scan', $totalScans) }} run so far see below how these sites stack up.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($showcaseAudits as $showcase)
                    @php
                        $host = parse_url($showcase->url, PHP_URL_HOST) ?? $showcase->url;
                        $categoryLabels = ['Legal' => 'LEG', 'Contact' => 'CON', 'Reliability' => 'REL', 'SEO' => 'SEO', 'Accessibility' => 'ACC'];
                        $scores = $showcase->categoryScores();
                    @endphp
                    <a href="{{ route('audits.show', $showcase) }}"
                       class="block bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all p-5">

                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-4xl font-black text-gray-900 leading-none">{{ $showcase->score }}</p>
                                <p class="text-[10px] tracking-wide text-gray-400 uppercase mt-1">/ 100</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide {{ \App\Support\Certification::badgeClasses($showcase->certification) }}">
                                {{ strtoupper($showcase->certification) }}
                            </span>
                        </div>

                        <p class="text-sm font-semibold text-gray-800 truncate mb-4">{{ $host }}</p>

                        <div class="space-y-1.5">
                            @foreach ($categoryLabels as $category => $label)
                                @php $pct = $scores[$category] ?? 0; @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-semibold text-gray-400 w-8 shrink-0">{{ $label }}</span>
                                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-400 w-7 text-right shrink-0">{{ $pct }}</span>
                                </div>
                            @endforeach
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <script>
        document.getElementById('scan-form').addEventListener('submit', function () {
            const btn = document.getElementById('scan-btn');
            const label = document.getElementById('scan-btn-label');
            const input = document.getElementById('url-input');

            btn.disabled = true;
            input.readOnly = true;

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