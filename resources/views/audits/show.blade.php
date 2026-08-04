<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRWS Report - {{ $audit->url }}</title>

    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Saira+Condensed:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        bg: '#020017',
                        'bg-deep': '#01000f',
                        surface: 'rgba(255, 255, 255, 0.03)',
                        line: 'rgba(255, 255, 255, 0.08)',
                        brand: '#6366f1',
                        'brand-mid': '#4f46e5',
                        'brand-hi': '#818cf8',
                    },
                    fontFamily: {
                        sans: ['"Instrument Sans"', 'sans-serif'],
                        display: ['"Bebas Neue"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                        cond: ['"Saira Condensed"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #020017;
            font-family: "Instrument Sans", sans-serif;
            overflow-x: hidden;
        }
        .glass-panel {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
        }
        .matrix-cell {
            animation: matrix-pulse var(--cell-dur) ease-in-out infinite;
        }
        @keyframes matrix-pulse {
            0%, 100% { opacity: 0.08; }
            50% { opacity: 0.35; fill: #6366f1; }
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 relative py-12 px-6">

    <!-- Background Gradients -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden bg-bg">
        <div class="absolute top-[-25%] left-[50%] -translate-x-1/2 w-[120%] h-[70%] bg-[radial-gradient(50%_60%_at_50%_0%,_rgba(99,102,241,0.12),_rgba(2,0,23,0.3)_60%,_rgba(2,0,23,0))]"></div>
        <div class="absolute bottom-[-10%] left-[10%] w-[45%] h-[50%] bg-[radial-gradient(closest-side,_rgba(168,85,247,0.04),_transparent_75%)] filter blur-3xl transform rotate-12"></div>
        
        <!-- Status Matrix lights bottom background -->
        <div class="absolute bottom-0 left-0 right-0 h-[15%] opacity-35 min-[720px]:block hidden" style="mask-image: linear-gradient(to top, #000, transparent); -webkit-mask-image: linear-gradient(to top, #000, transparent);">
            <div class="w-full h-full flex items-center justify-center">
                <svg width="480" height="80" viewBox="0 0 480 80" class="w-full max-w-[600px] h-full" aria-hidden="true">
                    @for ($r = 0; $r < 3; $r++)
                        @for ($c = 0; $c < 15; $c++)
                            @php
                                $x = $c * (24 + 8) + 20;
                                $y = $r * (12 + 8) + 10;
                                $dur = 1.8 + (($c + $r) % 5) * 0.5;
                            @endphp
                            <rect x="{{ $x }}" y="{{ $y }}" width="24" height="12" rx="3" fill="#2c2b54" class="matrix-cell" style="--cell-dur: {{ $dur }}s" />
                        @endfor
                    @endfor
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-3xl mx-auto relative z-10">

        <!-- Navigation / Actions Row -->
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('audits.create') }}" class="group font-mono text-slate-400 text-xs hover:text-white transition flex items-center gap-1.5 bg-white/5 border border-line px-3 py-2 rounded-xl">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                BACK TO SCANNER
            </a>

            <div class="flex items-center gap-2">
                <form action="{{ route('audits.rescan', $audit) }}" method="POST">
                    @csrf
                    <button type="submit" class="font-mono text-slate-300 text-xs hover:text-white transition flex items-center gap-1.5 bg-white/5 border border-line px-3.5 py-2 rounded-xl">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        RE-SCAN
                    </button>
                </form>

                <button type="button" onclick="copyReportLink(this)" class="font-mono text-slate-300 text-xs hover:text-white transition flex items-center gap-1.5 bg-white/5 border border-line px-3.5 py-2 rounded-xl">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                    </svg>
                    <span id="copy-link-label">COPY LINK</span>
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 text-sm rounded-xl p-4 mb-6 flex items-start gap-2.5">
                <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Score panel layout with radial gauge (glowing glass panel) -->
        <div class="glass-panel rounded-2xl p-8 mb-8 flex items-center gap-8 shadow-2xl relative overflow-hidden">
            <!-- Dynamic Glowing Auroras inside Score card -->
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-brand/10 rounded-full blur-2xl pointer-events-none"></div>

            @php
                use App\Support\Certification;

                $score = $audit->score ?? 0;
                $radius = 54;
                $circumference = 2 * M_PI * $radius;
                $offset = $circumference - ($score / 100) * $circumference;

                $gaugeColor = Certification::gaugeColor($score);
                
                // Certification badge specific dark theme styling adaptation
                $badgeClasses = \App\Support\Certification::badgeClasses($audit->certification);
                $badgeColor = 'bg-slate-800 text-slate-200 border-slate-700';
                if (str_contains($badgeClasses, 'text-yellow')) {
                    $badgeColor = 'bg-yellow-500/10 text-yellow-300 border-yellow-500/30';
                } elseif (str_contains($badgeClasses, 'text-gray') || str_contains($badgeClasses, 'text-slate')) {
                    $badgeColor = 'bg-slate-400/10 text-slate-300 border-slate-400/30';
                } elseif (str_contains($badgeClasses, 'text-orange') || str_contains($badgeClasses, 'text-amber')) {
                    $badgeColor = 'bg-amber-600/15 text-amber-400 border-amber-500/30';
                } elseif (str_contains($badgeClasses, 'text-teal') || str_contains($badgeClasses, 'text-emerald') || str_contains($badgeClasses, 'text-cyan')) {
                    $badgeColor = 'bg-cyan-500/10 text-cyan-300 border-cyan-500/30';
                }
            @endphp

            <div class="relative w-32 h-32 shrink-0 flex items-center justify-center">
                <!-- Outer glow shadow ring matching score gauge color -->
                <div class="absolute inset-0 rounded-full filter blur-[15px] opacity-[0.25]" style="background-color: {{ $gaugeColor }};"></div>
                
                <svg class="w-32 h-32 -rotate-90 absolute" viewBox="0 0 128 128">
                    <!-- Background circle -->
                    <circle cx="64" cy="64" r="{{ $radius }}" stroke="rgba(255,255,255,0.04)" stroke-width="9" fill="none" />
                    <!-- Animated score gauge path -->
                    <circle
                        cx="64" cy="64" r="{{ $radius }}"
                        stroke="{{ $gaugeColor }}"
                        stroke-width="9"
                        fill="none"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                        style="transition: stroke-dashoffset 0.8s cubic-bezier(0.25, 0.8, 0.25, 1);"
                    />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-black font-cond text-white tracking-tight leading-none">{{ $score }}</span>
                    <span class="text-[9px] font-mono text-slate-500 tracking-wider mt-0.5">/ 100</span>
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-slate-400 font-mono text-xs mb-2 truncate">{{ $audit->url }}</p>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-bold tracking-wider border {{ $badgeColor }}">
                    @if ($audit->certification !== 'None')
                        <!-- Hexagon checkmark icon -->
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.952 11.952 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    @endif
                    {{ $audit->certification }}
                </span>
            </div>
        </div>

        <!-- Category icon mappings -->
        @php
            $categoryIcons = [
                'Legal' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                'Contact' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
                'Reliability' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
                'SEO' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />',
                'Accessibility' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />',
                'Security' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />',
            ];
        @endphp

        <!-- Category Card List -->
        <div class="space-y-6">
            @foreach ($findings->groupBy('category') as $category => $categoryFindings)
                <div class="glass-panel rounded-2xl p-6 shadow-xl border border-line">
                    
                    <!-- Category Header -->
                    <h2 class="font-display text-xl tracking-wider text-white mb-5 flex items-center gap-2.5 uppercase border-b border-white/[0.04] pb-3">
                        <svg class="w-5 h-5 text-brand-hi" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {!! $categoryIcons[$category] ?? '<circle cx="12" cy="12" r="9" stroke-width="2" />' !!}
                        </svg>
                        {{ $category }}
                    </h2>
                    
                    <!-- Findings Items -->
                    <ul class="space-y-4">
                        @foreach ($categoryFindings as $finding)
                            <li class="flex items-center justify-between gap-4 font-sans py-1">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    @if ($finding->passed)
                                        <span class="w-6 h-6 flex items-center justify-center rounded-xl bg-green-500/10 text-green-400 border border-green-500/20 shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="w-6 h-6 flex items-center justify-center rounded-xl bg-red-500/10 text-red-400 border border-red-500/20 shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                    @endif
                                    
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-slate-200 truncate leading-snug">{{ $finding->name }}</p>
                                        <p class="text-[10px] text-slate-500 font-mono mt-0.5 tracking-tight">
                                            {{ $finding->rule_id }} · <span class="capitalize">{{ strtolower($finding->severity) }}</span>
                                        </p>
                                    </div>
                                </div>
                                
                                <span class="text-xs font-mono font-bold text-slate-400 bg-white/[0.03] border border-line px-2 py-1 rounded-lg shrink-0">
                                    {{ $finding->points_earned }} <span class="text-[9px] text-slate-600 font-semibold">/</span> {{ $finding->points_available }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

    </div>

    <!-- Script Handling Copy Link Trigger -->
    <script>
        function copyReportLink(btn) {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const label = document.getElementById('copy-link-label');
                const original = label.textContent;
                label.textContent = 'COPIED!';
                setTimeout(() => { label.textContent = original; }, 1500);
            });
        }
    </script>
</body>
</html>
