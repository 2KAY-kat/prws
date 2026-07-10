<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PRWS - Production Readiness Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .scan-card {
            transition: transform 280ms ease, box-shadow 280ms ease;
        }
        .scan-card:hover {
            transform: translate(-50%, -50%) scale(1.15) rotate(0deg) !important;
            z-index: 100 !important;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.45);
        }
    </style>
</head>
<body class="h-screen overflow-hidden bg-gray-50">
    <div class="h-full flex flex-col lg:flex-row items-center justify-center gap-16 px-8 lg:px-20">

        <div class="w-full max-w-md shrink-0">
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
                <input type="url" name="url" id="url-input" placeholder="https://yourwebsite.com" value="{{ old('url') }}" required class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" id="scan-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-lg transition disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2 min-w-[110px] justify-center">
                    <span id="scan-btn-label">Scan</span>
                </button>
            </form>

            <p class="text-xs text-gray-400 mt-6">Checks 13 basic production-readiness rules: legal pages, contact info, SEO basics, reliability, and accessibility.</p>

            @if ($totalScans > 0)
                <p class="text-xs text-gray-400 mt-3 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    {{ number_format($totalScans) }} {{ Str::plural('scan', $totalScans) }} run so far
                </p>
            @endif
        </div>

        @if ($showcaseCards->isNotEmpty())
            <div class="relative shrink-0" style="width: 380px; height: 320px;">
                @php $count = $showcaseCards->count(); $mid = ($count - 1) / 2; @endphp

                @foreach ($showcaseCards as $index => $showcase)
                    @php
                        $offset = $index - $mid;
                        $rotateDeg = (int) round($offset * 7);
                        $translateX = (int) round($offset * 36);
                        $translateY = abs((int) round($offset)) * 8;
                        $zIndex = 10 + $index;
                        $host = parse_url($showcase->url, PHP_URL_HOST) ?? $showcase->url;
                        $categoryLabels = ['Legal' => 'LEG', 'Contact' => 'CON', 'Reliability' => 'REL', 'SEO' => 'SEO', 'Accessibility' => 'ACC'];
                        $scores = $showcase->categoryScores();
                    @endphp
                    <a href="{{ route('audits.show', $showcase) }}" class="scan-card absolute top-1/2 left-1/2 w-48 rounded-2xl shadow-xl ring-1 ring-black/10 p-4 {{ \App\Support\Certification::cardGradient($showcase->certification) }}" style="z-index: {{ $zIndex }}; transform: translate(-50%, -50%) translateX({{ $translateX }}px) translateY({{ $translateY }}px) rotate({{ $rotateDeg }}deg);">

                        <div class="flex justify-center mb-2">
                            <svg class="w-4 h-4 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 1a6 6 0 00-3.815 10.631C7.98 12.83 9 14.552 9 16.5V17a1 1 0 001 1h0a1 1 0 001-1v-.5c0-1.948 1.02-3.67 2.815-4.869A6 6 0 0010 1z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="text-3xl font-black leading-none">{{ $showcase->score }}</p>
                                <p class="text-[9px] font-bold tracking-widest uppercase opacity-70 mt-1">{{ $showcase->certification }}</p>
                            </div>
                            <div class="relative w-10 h-10 shrink-0">
                                <img src="https://www.google.com/s2/favicons?domain={{ $host }}&sz=64" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');" class="w-10 h-10 rounded-lg bg-white/70 p-1 shadow-sm object-contain" alt="{{ $host }} favicon">
                                <div class="hidden w-10 h-10 rounded-lg bg-white/40 shadow-sm items-center justify-center text-sm font-bold absolute inset-0">{{ strtoupper(substr($host, 0, 1)) }}</div>
                            </div>
                        </div>

                        <p class="text-xs font-bold truncate mb-3">{{ $host }}</p>

                        <div class="grid grid-cols-2 gap-x-2 gap-y-1 pt-2 border-t border-black/10">
                            @foreach ($categoryLabels as $category => $label)
                                @php $pct = $scores[$category] ?? 0; @endphp
                                <div class="flex items-center justify-between text-[9px] font-semibold">
                                    <span class="opacity-60">{{ $label }}</span>
                                    <span>{{ $pct }}</span>
                                </div>
                            @endforeach
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>

    <script>
        document.getElementById('scan-form').addEventListener('submit', function () {
            const btn = document.getElementById('scan-btn');
            const label = document.getElementById('scan-btn-label');
            const input = document.getElementById('url-input');
            btn.disabled = true;
            input.readOnly = true;
            label.innerHTML = `<svg class="animate-spin h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="ml-1">Scanning...</span>`;
            label.classList.add('flex', 'items-center');
        });
    </script>
</body>
</html>