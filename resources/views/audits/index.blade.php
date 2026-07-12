<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRWS - Scan History</title>
    
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
        
        /* Custom pagination overrides to make it look premium dark theme */
        .pagination nav {
            background: transparent !important;
            border: none !important;
        }
        .pagination span, .pagination a {
            color: #cbd5e1 !important;
            background-color: rgba(255, 255, 255, 0.03) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .pagination a:hover {
            background-color: rgba(99, 102, 241, 0.2) !important;
            color: #ffffff !important;
        }
        .pagination .active span {
            background-color: #4f46e5 !important;
            border-color: #6366f1 !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 relative py-16 px-6">

    <!-- Background Gradients -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden bg-bg">
        <div class="absolute top-[-25%] left-[50%] -translate-x-1/2 w-[120%] h-[70%] bg-[radial-gradient(50%_60%_at_50%_0%,_rgba(99,102,241,0.1),_rgba(2,0,23,0.3)_60%,_rgba(2,0,23,0))]"></div>
        <div class="absolute bottom-[-10%] right-[10%] w-[45%] h-[50%] bg-[radial-gradient(closest-side,_rgba(168,85,247,0.04),_transparent_75%)] filter blur-3xl transform rotate-12"></div>
        
        <!-- Status Matrix lights bottom background -->
        <div class="absolute bottom-0 left-0 right-0 h-[15%] opacity-30 min-[720px]:block hidden" style="mask-image: linear-gradient(to top, #000, transparent); -webkit-mask-image: linear-gradient(to top, #000, transparent);">
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

        <!-- Header Row -->
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="font-display text-4xl tracking-wider text-white uppercase">SCAN HISTORY</h1>
                <p class="text-xs text-slate-400 font-mono mt-1">Systematic production readiness records</p>
            </div>
            
            <a href="{{ route('audits.create') }}"
               class="group font-mono inline-flex items-center gap-1.5 bg-brand hover:bg-brand-mid text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition duration-200 shadow-[0_0_20px_rgba(99,102,241,0.25)] hover:shadow-[0_0_30px_rgba(99,102,241,0.4)]">
                <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                NEW SCAN
            </a>
        </div>

        <!-- History Records list -->
        @if ($audits->isEmpty())
            <div class="glass-panel rounded-2xl p-12 text-center text-slate-400 font-mono text-sm shadow-xl">
                <div class="flex justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-500 opacity-55" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                No scans executed yet.
            </div>
        @else
            <div class="glass-panel rounded-2xl shadow-xl overflow-hidden divide-y divide-white/[0.06]">
                @foreach ($audits as $audit)
                    @php
                        $host = parse_url($audit->url, PHP_URL_HOST) ?? $audit->url;
                        $badgeClasses = \App\Support\Certification::badgeClasses($audit->certification);
                        
                        // Adapt default Laravel classes to fit dark futuristic aesthetic
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
                    
                    <a href="{{ route('audits.show', $audit) }}"
                       class="flex items-center justify-between px-6 py-5 hover:bg-white/[0.03] transition-all duration-200 group">
                        
                        <!-- Details on Left -->
                        <div class="min-w-0 flex items-center gap-4">
                            <!-- Host Favicon or Initial Icon -->
                            <div class="relative w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-bg-deep border border-line p-1 group-hover:border-brand/40 transition">
                                <img src="https://www.google.com/s2/favicons?domain={{ $host }}&sz=64" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');" class="w-6 h-6 object-contain" alt="{{ $host }} favicon">
                                <div class="hidden w-6 h-6 rounded bg-white/10 flex items-center justify-center text-xs font-bold text-slate-300">{{ strtoupper(substr($host, 0, 1)) }}</div>
                            </div>
                            
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-200 truncate group-hover:text-brand-hi transition font-mono">{{ $audit->url }}</p>
                                <p class="text-xs text-slate-500 mt-1 font-mono">{{ $audit->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <!-- Grades & Badges on Right -->
                        <div class="flex items-center gap-4 shrink-0 ml-4">
                            <div class="text-right">
                                <p class="text-lg font-bold font-cond tracking-wide text-white group-hover:text-brand-hi transition">{{ $audit->score }}<span class="text-[10px] text-slate-500 font-mono">/100</span></p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold tracking-wider uppercase border {{ $badgeColor }}">
                                {{ $audit->certification }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination component -->
            <div class="mt-8 pagination font-mono">
                {{ $audits->links() }}
            </div>
        @endif

    </div>
</body>
</html>