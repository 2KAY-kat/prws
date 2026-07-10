<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PRWS - Production Readiness Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: radial-gradient(circle at 15% 20%, rgba(99, 102, 241, 0.25), transparent 45%),
                        radial-gradient(circle at 85% 80%, rgba(56, 189, 248, 0.15), transparent 50%),
                        linear-gradient(180deg, #0b0a1f 0%, #100e2b 60%, #0b0a1f 100%);
        }
        .scan-card {
            transition: transform 280ms ease, box-shadow 280ms ease;
        }
        .scan-card:hover {
            transform: translate(-50%, -50%) scale(1.15) rotate(0deg) !important;
            z-index: 100 !important;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.6);
        }
        .modal-backdrop {
            backdrop-filter: blur(4px);
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.15); opacity: 0.1; }
            100% { transform: scale(0.9); opacity: 0.6; }
        }
        .pulse-ring { animation: pulse-ring 1.8s ease-in-out infinite; }
    </style>
</head>
<body class="h-screen overflow-hidden">
    <div class="h-full flex flex-col lg:flex-row items-center justify-center gap-16 px-8 lg:px-20">

        {{-- Left: hero + scan form --}}
        <div class="w-full max-w-md shrink-0">

            <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-full px-3 py-1 mb-5">
                <span class="text-[10px] font-bold tracking-widest text-indigo-300 uppercase">PRWS</span>
                <span class="text-white/20">×</span>
                <span class="text-[10px] font-bold tracking-widest text-white/60 uppercase">Vibe Coding Era</span>
            </div>

            <h1 class="text-5xl font-black text-white leading-[0.95] tracking-tight mb-4">
                SHIP IT<br>READY<span class="text-indigo-400">.</span>
            </h1>

            <p class="text-white/50 mb-8 leading-relaxed">
                Your website's production-readiness, scored out of 100 and rated Bronze to Platinum.
            </p>

            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/30 text-red-300 text-sm rounded-lg p-3 mb-4 flex items-start gap-2">
                    <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    class="flex-1 bg-white/5 border border-white/10 text-white placeholder-white/30 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                >
                <button
                    type="submit"
                    id="scan-btn"
                    class="bg-indigo-500 hover:bg-indigo-400 text-white font-bold px-6 py-3 rounded-lg transition disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    SCAN
                </button>
            </form>

            <p class="text-xs text-white/30 mt-4">
                <a href="{{ route('audits.index') }}" class="hover:text-white/60 transition">History</a>
            </p>

            <div class="flex items-center gap-3 mt-6 text-xs text-white/40">
                @if ($totalScans > 0)
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                        <span class="font-semibold text-white/70">{{ number_format($totalScans) }}</span> {{ Str::plural('scan', $totalScans) }} run
                    </span>
                    <span class="text-white/20">·</span>
                @endif
                <button type="button" onclick="document.getElementById('how-it-works-modal').classList.remove('hidden')" class="hover:text-white/70 transition">
                    how it works ↗
                </button>
            </div>
        </div>

        {{-- Right: fanned tier cards --}}
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
                    <a href="{{ route('audits.show', $showcase) }}" class="scan-card absolute top-1/2 left-1/2 w-48 rounded-2xl shadow-xl ring-1 ring-white/10 p-4 {{ \App\Support\Certification::cardGradient($showcase->certification) }}" style="z-index: {{ $zIndex }}; transform: translate(-50%, -50%) translateX({{ $translateX }}px) translateY({{ $translateY }}px) rotate({{ $rotateDeg }}deg);">

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

    {{-- How it works modal --}}
    <div id="how-it-works-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center px-6">
        <div class="modal-backdrop absolute inset-0 bg-black/60" onclick="document.getElementById('how-it-works-modal').classList.add('hidden')"></div>
        <div class="relative bg-[#100e2b] border border-white/10 rounded-2xl max-w-md w-full p-8 shadow-2xl">
            <button type="button" onclick="document.getElementById('how-it-works-modal').classList.add('hidden')" class="absolute top-5 right-5 text-white/40 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="text-xl font-black text-white mb-6">HOW IT WORKS</h2>
            <div class="space-y-5">
                <div class="flex gap-4">
                    <div class="w-7 h-7 rounded-full bg-indigo-500 text-white text-xs font-bold flex items-center justify-center shrink-0">1</div>
                    <div>
                        <p class="text-white text-sm font-semibold">Submit a URL</p>
                        <p class="text-white/40 text-xs mt-0.5">Drop in any website - we fetch the homepage plus known paths like /privacy and /robots.txt.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-7 h-7 rounded-full bg-indigo-500 text-white text-xs font-bold flex items-center justify-center shrink-0">2</div>
                    <div>
                        <p class="text-white text-sm font-semibold">We run 13 checks</p>
                        <p class="text-white/40 text-xs mt-0.5">Across 5 categories - Legal, Contact, Reliability, SEO, and Accessibility.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-7 h-7 rounded-full bg-indigo-500 text-white text-xs font-bold flex items-center justify-center shrink-0">3</div>
                    <div>
                        <p class="text-white text-sm font-semibold">Score out of 100</p>
                        <p class="text-white/40 text-xs mt-0.5">Each rule carries points; missing critical items (like no privacy policy) hit your score hardest.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-7 h-7 rounded-full bg-indigo-500 text-white text-xs font-bold flex items-center justify-center shrink-0">4</div>
                    <div>
                        <p class="text-white text-sm font-semibold">Get certified</p>
                        <p class="text-white/40 text-xs mt-0.5">Bronze, Silver, Gold, or Platinum - based on your score and whether critical issues remain.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scanning overlay --}}
    <div id="scan-overlay" class="hidden fixed inset-0 z-[300] flex flex-col items-center justify-center">
        <div class="absolute inset-0" style="background: radial-gradient(circle at 50% 30%, rgba(99, 102, 241, 0.2), transparent 55%), #0b0a1f;"></div>

        <div class="relative flex flex-col items-center px-6 text-center">
            <div class="relative w-24 h-24 mb-8">
                <div class="pulse-ring absolute inset-0 rounded-full bg-indigo-500/30"></div>
                <div class="relative w-24 h-24 rounded-full bg-indigo-500/10 border border-indigo-400/30 flex items-center justify-center">
                    <svg class="w-10 h-10 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <h2 class="text-2xl font-black text-white mb-1">
                SCANNING <span id="scan-overlay-host" class="text-indigo-400"></span>
            </h2>
            <p id="scan-overlay-step" class="text-white/40 text-sm mb-8 h-5 transition-opacity duration-200">Fetching homepage...</p>

            <div class="w-72 h-1 bg-white/10 rounded-full overflow-hidden">
                <div id="scan-overlay-bar" class="h-full bg-indigo-500 rounded-full transition-all duration-500 ease-out" style="width: 5%"></div>
            </div>
        </div>
    </div>

    <script>
        const scanSteps = [
            'Fetching homepage...',
            'Checking legal pages...',
            'Checking contact info...',
            'Testing reliability & 404s...',
            'Scanning SEO basics...',
            'Checking accessibility...',
            'Calculating your score...',
        ];

        document.getElementById('scan-form').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const urlInput = document.getElementById('url-input');
            const rawUrl = urlInput.value;

            let host = rawUrl;
            try { host = new URL(rawUrl).hostname; } catch (err) {}

            const overlay = document.getElementById('scan-overlay');
            const stepLabel = document.getElementById('scan-overlay-step');
            const bar = document.getElementById('scan-overlay-bar');
            document.getElementById('scan-overlay-host').textContent = host;

            overlay.classList.remove('hidden');

            let stepIndex = 0;
            const totalSteps = scanSteps.length;

            const stepInterval = setInterval(() => {
                stepIndex = Math.min(stepIndex + 1, totalSteps - 1);
                stepLabel.style.opacity = 0;
                setTimeout(() => {
                    stepLabel.textContent = scanSteps[stepIndex];
                    stepLabel.style.opacity = 1;
                }, 200);

                const pct = Math.min(90, Math.round(((stepIndex + 1) / totalSteps) * 90));
                bar.style.width = pct + '%';
            }, 650);

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(response => {
                    clearInterval(stepInterval);
                    bar.style.width = '100%';
                    stepLabel.textContent = 'Done!';
                    setTimeout(() => {
                        window.location.href = response.url;
                    }, 300);
                })
                .catch(() => {
                    clearInterval(stepInterval);
                    window.location.reload();
                });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.getElementById('how-it-works-modal').classList.add('hidden');
            }
        });
    </script>
</body>
</html>