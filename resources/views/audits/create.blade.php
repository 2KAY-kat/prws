<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRWS - Production Readiness Scanner</title>
    
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
                        gold: '#d4af37',
                        'gold-hi': '#ffd700',
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

        .animate-float {
            animation: float 5s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(2deg); }
        }

        .animate-pulse-slow {
            animation: pulse-slow 3s ease-in-out infinite;
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.15; }
            50% { opacity: 0.4; }
        }

        .pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 0.2; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }

        /* Showcase Cards */
        .scan-card {
            transition: all 400ms cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .scan-card:hover {
            transform: translate(-50%, -55%) scale(1.15) rotate(0deg) !important;
            z-index: 100 !important;
            box-shadow: 0 35px 70px -10px rgba(99, 102, 241, 0.25), 0 0 40px rgba(99, 102, 241, 0.15);
        }

        .glow-button {
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.4), 0 10px 30px rgba(99, 102, 241, 0.25);
        }
        .glow-button:hover {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.6), 0 10px 40px rgba(99, 102, 241, 0.4);
        }

        /* Glass Backdrop */
        .glass-panel {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Checker Matrix pulse animation */
        .matrix-cell {
            animation: matrix-pulse var(--cell-dur) ease-in-out infinite;
        }
        @keyframes matrix-pulse {
            0%, 100% { opacity: 0.08; }
            50% { opacity: 0.45; fill: #6366f1; }
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 relative">

    <!-- Background Gradients -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden bg-bg">
        <!-- Top radial glow (Brand/Indigo) -->
        <div class="absolute top-[-25%] left-[50%] -translate-x-1/2 w-[120%] h-[80%] bg-[radial-gradient(50%_60%_at_50%_0%,_rgba(99,102,241,0.14),_rgba(2,0,23,0.3)_60%,_rgba(2,0,23,0))]"></div>
        <!-- Side soft glows -->
        <div class="absolute top-[10%] left-[5%] w-[40%] h-[60%] bg-[radial-gradient(closest-side,_rgba(99,102,241,0.08),_transparent_75%)] filter blur-3xl transform rotate-12"></div>
        <div class="absolute top-[15%] right-[5%] w-[35%] h-[60%] bg-[radial-gradient(closest-side,_rgba(168,85,247,0.06),_transparent_75%)] filter blur-3xl transform -rotate-12"></div>
        
        <!-- Audit Check matrix (Systematic status lights) -->
        <div class="absolute bottom-0 left-0 right-0 h-[20%] opacity-40 min-[980px]:block hidden" style="mask-image: linear-gradient(to top, #000, transparent); -webkit-mask-image: linear-gradient(to top, #000, transparent);">
            <div class="w-full h-full flex items-center justify-center">
                <svg width="640" height="120" viewBox="0 0 640 120" class="w-full max-w-[800px] h-full" aria-hidden="true">
                    @php
                        $cols = 20;
                        $rows = 4;
                        $cellW = 22;
                        $cellH = 14;
                        $gap = 8;
                    @endphp
                    @for ($r = 0; $r < $rows; $r++)
                        @for ($c = 0; $c < $cols; $c++)
                            @php
                                $x = $c * ($cellW + $gap) + 20;
                                $y = $r * ($cellH + $gap) + 15;
                                $dur = 2.0 + (($c + $r) % 5) * 0.6;
                            @endphp
                            <rect 
                                x="{{ $x }}" y="{{ $y }}" 
                                width="{{ $cellW }}" height="{{ $cellH }}" 
                                rx="3" 
                                fill="#2c2b54" 
                                class="matrix-cell" 
                                style="--cell-dur: {{ $dur }}s"
                            />
                        @endfor
                    @endfor
                </svg>
            </div>
        </div>

        <!-- Subtle overlay grain -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml;utf8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22120%22%20height%3D%22120%22%3E%3Cfilter%20id%3D%22n%22%3E%3CfeTurbulence%20type%3D%22fractalNoise%22%20baseFrequency%3D%220.85%22%20numOctaves%3D%222%22%2F%3E%3C%2Ffilter%3E%3Crect%20width%3D%22120%22%20height%3D%22120%22%20filter%3D%22url(%23n)%22%2F%3E%3C%2Fsvg%3E'); mix-blend-mode: overlay;"></div>
    </div>

    <!-- Main Navigation / Headers -->
    <header class="relative z-10">
        <div class="absolute right-[clamp(20px,5vw,52px)] top-[clamp(16px,3vh,26px)]">
            <a href="https://github.com/2KAY-kat/prws" target="_blank" rel="noopener" aria-label="View PRWS on GitHub" class="group inline-flex items-center gap-[8px] rounded-full border border-line bg-bg-deep/55 py-[7px] pl-[13px] pr-[7px] text-[13px] font-semibold text-slate-400 backdrop-blur-md transition duration-200 hover:-translate-y-px hover:border-slate-500 hover:bg-bg-deep/80 hover:text-slate-100">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="shrink-0">
                    <path d="M12 .5C5.37.5 0 5.87 0 12.5c0 5.3 3.44 9.8 8.21 11.39.6.11.82-.26.82-.58 0-.29-.01-1.04-.02-2.05-3.34.73-4.04-1.61-4.04-1.61-.55-1.39-1.34-1.76-1.34-1.76-1.09-.75.08-.74.08-.74 1.21.09 1.84 1.24 1.84 1.24 1.07 1.84 2.81 1.31 3.5 1 .11-.78.42-1.31.76-1.61-2.67-.3-5.47-1.34-5.47-5.95 0-1.31.47-2.39 1.24-3.23-.12-.3-.54-1.52.12-3.18 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 6 0c2.29-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.24 1.92 1.24 3.23 0 4.62-2.81 5.64-5.49 5.94.43.37.81 1.1.81 2.22 0 1.6-.01 2.89-.01 3.29 0 .32.22.7.83.58A12.01 12.01 0 0 0 24 12.5C24 5.87 18.63.5 12 .5z"></path>
                </svg>
                <span class="max-[520px]:hidden">View on GitHub</span>
                <span class="inline-flex items-center gap-[4px] rounded-full bg-white/[0.06] px-[8px] py-[3px] leading-none text-slate-500 transition group-hover:bg-white/[0.1] group-hover:text-slate-200">
                    <span class="font-mono text-[12px]">v1.0</span>
                </span>
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="relative z-10 flex min-h-screen flex-col justify-center">
        <div class="mx-auto flex w-full max-w-[1180px] items-center gap-[clamp(24px,5vw,72px)] px-[clamp(22px,5vw,56px)] max-[860px]:flex-col max-[860px]:gap-[34px] max-[860px]:pb-12 max-[860px]:pt-[clamp(48px,8vh,72px)] max-[860px]:text-center">
            
            <!-- Left: Hero + Scan Form -->
            <div class="min-w-0 flex-1">
                
                <!-- Floating Mascot (Production Readiness Scan Shield Bot) -->
                <div class="mb-4 max-[860px]:mx-auto max-[860px]:flex max-[860px]:justify-center">
                    <div class="animate-float relative w-24 h-24 flex items-center justify-center">
                        <!-- Orbit Rings -->
                        <div class="absolute inset-0 border-[1.5px] border-dashed border-brand/30 rounded-full animate-[spin_10s_linear_infinite]"></div>
                        <div class="absolute inset-2 border border-brand-hi/20 rounded-full animate-[spin_6s_linear_infinite_reverse]"></div>
                        <!-- Shield Core -->
                        <svg class="w-14 h-14 text-brand filter drop-shadow-[0_0_15px_rgba(99,102,241,0.6)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="m9 12 2 2 4-4" stroke-width="2"/>
                        </svg>
                        <!-- Glow Dots -->
                        <div class="absolute top-1 left-1 w-2.5 h-2.5 bg-brand-hi rounded-full shadow-[0_0_8px_#818cf8]"></div>
                        <div class="absolute bottom-1 right-1 w-2 h-2 bg-purple-400 rounded-full shadow-[0_0_8px_#c084fc]"></div>
                    </div>
                </div>

                <!-- Tagline Badge -->
                <div class="mb-[18px] inline-flex items-center gap-[9px] rounded-[8px] border border-white/[0.08] bg-white/[0.025] px-[12px] py-[6px] max-[860px]:mx-auto">
                    <span class="font-mono text-[10.5px] font-semibold tracking-[.18em] text-slate-400">PRWS</span>
                    <span class="font-display text-[15px] mt-[1px] leading-none text-brand">×</span>
                    <span class="font-mono text-[10.5px] tracking-[.06em] text-slate-200">VIBE CODING ERA</span>
                </div>

                <!-- Main Display Header -->
                <h1 class="font-display m-0 mb-3 text-[clamp(44px,7vw,92px)] leading-[0.85] tracking-[.005em] uppercase text-white">
                    SHIP IT<br>READY<span class="text-brand">.</span>
                </h1>

                <!-- Subheading Description -->
                <p class="mb-[26px] max-w-[420px] text-[clamp(15px,1.7vw,18px)] font-medium leading-[1.5] text-slate-400 max-[860px]:mx-auto">
                    Your website's production-readiness, scored out of 100 and rated Bronze to Platinum.
                </p>

                <!-- Scan Input Form -->
                <form action="{{ route('audits.store') }}" method="POST" class="m-0 flex max-w-[460px] flex-wrap gap-[10px] max-[860px]:mx-auto" id="scan-form">
                    @csrf
                    <div class="relative min-w-[200px] flex-1">
                        <input 
                            type="url"
                            name="url"
                            id="url-input"
                            placeholder="https://yourwebsite.com" 
                            autoComplete="off" 
                            spellCheck="false" 
                            aria-label="Website URL"
                            value="{{ old('url') }}"
                            required
                            class="font-mono h-14 w-full rounded-[14px] border-[1.5px] border-line bg-bg-deep/60 pl-5 pr-5 text-[15px] font-medium text-white outline-none backdrop-blur-[4px] transition focus:border-brand focus:bg-bg-deep focus:shadow-[0_0_0_4px_rgba(99,102,241,0.16),0_0_42px_rgba(99,102,241,0.24)]"
                        />
                    </div>
                    <button 
                        type="submit" 
                        id="scan-btn"
                        class="font-display group glow-button flex h-14 items-center gap-2 rounded-[14px] bg-gradient-to-b from-brand to-brand-mid px-7 text-[20px] tracking-[.06em] text-white transition hover:from-brand-hi hover:to-brand disabled:cursor-wait disabled:opacity-75"
                    >
                        SCAN
                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right transition-transform group-hover:translate-x-0.5" aria-hidden="true">
                            <path d="M5 12h14"></path>
                            <path d="m12 5 7 7-7 7"></path>
                        </svg>
                    </button>
                </form>

                <!-- Try Quick Links -->
                <div class="mt-[14px] text-[13px] text-slate-500">
                    try
                    <button type="button" onclick="document.getElementById('url-input').value = 'https://laravel.com'" class="cursor-pointer font-mono text-slate-300 underline decoration-brand/40 underline-offset-[3px] transition hover:text-brand">laravel.com</button> 
                    · 
                    <button type="button" onclick="document.getElementById('url-input').value = 'https://tailwindcss.com'" class="cursor-pointer font-mono text-slate-300 underline decoration-brand/40 underline-offset-[3px] transition hover:text-brand">tailwindcss.com</button> 
                    · or your own
                </div>

                <!-- Stats & Modal Trigger -->
                <div class="mt-[20px] flex flex-wrap items-center gap-x-[14px] gap-y-[10px] max-[860px]:justify-center">
                    @if ($totalScans > 0)
                        <span class="inline-flex items-baseline gap-[9px]">
                            <span class="relative flex h-[7px] w-[7px] translate-y-[-1px] self-center" aria-hidden="true">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand opacity-60"></span>
                                <span class="relative inline-flex h-[7px] w-[7px] rounded-full bg-brand"></span>
                            </span>
                            <span class="font-display text-[20px] leading-none tabular-nums text-white">{{ number_format($totalScans) }}</span>
                            <span class="text-[12px] text-slate-500">{{ Str::plural('scan', $totalScans) }} rated</span>
                        </span>
                        <span aria-hidden="true" class="h-[12px] w-px bg-white/[0.12] max-[860px]:hidden"></span>
                    @endif
                    <a href="{{ route('audits.index') }}" class="cursor-pointer text-[12.5px] font-semibold text-slate-300 hover:text-brand underline underline-offset-2">history ↗</a>
                    <span aria-hidden="true" class="h-[12px] w-px bg-white/[0.12]"></span>
                    <button type="button" onclick="document.getElementById('how-it-works-modal').classList.remove('hidden')" class="cursor-pointer text-[12.5px] font-semibold text-slate-300 hover:text-brand hover:underline">how it works ↗</button>
                </div>
            </div>

            <!-- Right: Fanned Tier Cards -->
            @if ($showcaseCards->isNotEmpty())
                <div class="relative flex min-w-0 flex-[1.12] items-center justify-center max-[860px]:w-full max-[860px]:mt-6">
                    <div aria-hidden="true" class="pointer-events-none absolute inset-0 flex items-center justify-center max-[860px]:hidden">
                        <!-- Background large transparent "100" grade watermark -->
                        <div class="font-display font-black leading-[.8] text-transparent" style="font-size: clamp(170px, 22vw, 300px); -webkit-text-stroke: 1.4px rgba(255, 255, 255, 0.035);">100</div>
                    </div>
                    
                    <div class="relative h-[360px] w-[min(600px,98%)] max-[860px]:flex max-[860px]:h-auto max-[860px]:w-full max-[860px]:flex-col max-[860px]:items-center max-[860px]:gap-[18px]">
                        @php 
                            $count = $showcaseCards->count(); 
                            $mid = ($count - 1) / 2; 
                        @endphp

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
                                $gradient = \App\Support\Certification::cardGradient($showcase->certification);
                            @endphp
                            
                            <div class="absolute left-1/2 top-[18px] w-[184px] origin-bottom cursor-pointer scan-card max-[860px]:static max-[860px]:w-[min(230px,66vw)] max-[860px]:!transform-none max-[860px]:!z-auto" 
                                 style="transform: translateX(-50%) translate({{ $translateX }}px, {{ $translateY }}px) rotate({{ $rotateDeg }}deg); z-index: {{ $zIndex }};">
                                
                                <a href="{{ route('audits.show', $showcase) }}" class="block w-full rounded-2xl shadow-xl ring-1 ring-white/10 p-4 border border-white/5 {{ $gradient }}">
                                    
                                    <!-- Badge Top SVG Icon (glowing hexagon check) -->
                                    <div class="flex justify-center mb-2">
                                        <svg class="w-4 h-4 opacity-60 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.952 11.952 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>

                                    <!-- Main stats block inside card -->
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <p class="text-3xl font-black font-cond leading-none tracking-tight text-white">{{ $showcase->score }}</p>
                                            <p class="text-[9px] font-bold font-mono tracking-widest uppercase text-slate-100 opacity-90 mt-1">{{ $showcase->certification }}</p>
                                        </div>
                                        <div class="relative w-10 h-10 shrink-0">
                                            <img src="https://www.google.com/s2/favicons?domain={{ $host }}&sz=64" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');" class="w-10 h-10 rounded-lg bg-bg-deep/75 p-1.5 shadow-sm border border-line object-contain" alt="{{ $host }} favicon">
                                            <div class="hidden w-10 h-10 rounded-lg bg-white/10 shadow-sm flex items-center justify-center text-sm font-bold absolute inset-0 text-white">{{ strtoupper(substr($host, 0, 1)) }}</div>
                                        </div>
                                    </div>

                                    <!-- Host Name -->
                                    <p class="text-[11px] font-mono truncate text-white mb-3 font-semibold">{{ $host }}</p>

                                    <!-- Category Scores Grid -->
                                    <div class="grid grid-cols-2 gap-x-2 gap-y-1 pt-2 border-t border-white/10 text-white font-mono">
                                        @foreach ($categoryLabels as $category => $label)
                                            @php $pct = $scores[$category] ?? 0; @endphp
                                            <div class="flex items-center justify-between text-[9px] font-semibold">
                                                <span class="opacity-60">{{ $label }}</span>
                                                <span class="font-bold">{{ $pct }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </main>

    <!-- How It Works Modal -->
    <div id="how-it-works-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center px-6">
        <div class="absolute inset-0 bg-black/70 modal-backdrop backdrop-blur-sm" onclick="document.getElementById('how-it-works-modal').classList.add('hidden')"></div>
        <div class="relative bg-bg border border-line rounded-2xl max-w-md w-full p-8 shadow-2xl z-10 glass-panel">
            <button type="button" onclick="document.getElementById('how-it-works-modal').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="font-display text-2xl tracking-[.06em] text-white mb-6 uppercase">HOW IT WORKS</h2>
            <div class="space-y-5">
                <div class="flex gap-4">
                    <div class="w-7 h-7 rounded-full bg-brand text-white text-xs font-bold flex items-center justify-center shrink-0">1</div>
                    <div>
                        <p class="text-white text-sm font-semibold">Submit a URL</p>
                        <p class="text-slate-400 text-xs mt-0.5">Drop in any website - we fetch the homepage plus known paths like /privacy and /robots.txt.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-7 h-7 rounded-full bg-brand text-white text-xs font-bold flex items-center justify-center shrink-0">2</div>
                    <div>
                        <p class="text-white text-sm font-semibold">We run 13 checks</p>
                        <p class="text-slate-400 text-xs mt-0.5">Across 5 categories - Legal, Contact, Reliability, SEO, and Accessibility.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-7 h-7 rounded-full bg-brand text-white text-xs font-bold flex items-center justify-center shrink-0">3</div>
                    <div>
                        <p class="text-white text-sm font-semibold">Score out of 100</p>
                        <p class="text-slate-400 text-xs mt-0.5">Each rule carries points; missing critical items (like no privacy policy) hit your score hardest.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-7 h-7 rounded-full bg-brand text-white text-xs font-bold flex items-center justify-center shrink-0">4</div>
                    <div>
                        <p class="text-white text-sm font-semibold">Get certified</p>
                        <p class="text-slate-400 text-xs mt-0.5">Bronze, Silver, Gold, or Platinum - based on your score and whether critical issues remain.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanning Overlay (Simulating GitFut load presentation) -->
    <div id="scan-overlay" class="hidden fixed inset-0 z-[300] flex flex-col items-center justify-center bg-bg">
        <!-- Radial Scanning Glow -->
        <div class="absolute inset-0" style="background: radial-gradient(circle at 50% 30%, rgba(99, 102, 241, 0.18), transparent 55%), #020017;"></div>

        <!-- Scanning Core Panel -->
        <div class="relative flex flex-col items-center px-6 text-center z-10">
            <!-- Animated Shield Radar -->
            <div class="relative w-28 h-28 mb-8">
                <div class="pulse-ring absolute inset-0 rounded-full bg-brand/20"></div>
                <div class="relative w-28 h-28 rounded-full bg-bg-deep border border-brand/30 flex items-center justify-center shadow-[0_0_30px_rgba(99,102,241,0.15)]">
                    <svg class="w-12 h-12 text-brand-hi animate-pulse-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Scanning Status Headline -->
            <h2 class="text-3xl font-black font-cond uppercase text-white mb-2 tracking-[0.03em]">
                SCANNING <span id="scan-overlay-host" class="text-brand-hi"></span>
            </h2>
            <p id="scan-overlay-step" class="text-slate-400 font-mono text-sm mb-8 h-5 transition-opacity duration-200">Preparing scanner...</p>

            <!-- Custom Scanning Progress Bar -->
            <div class="w-72 h-1.5 bg-white/10 rounded-full overflow-hidden">
                <div id="scan-overlay-bar" class="h-full bg-brand rounded-full transition-all duration-500 ease-out" style="width: 5%"></div>
            </div>
        </div>

        <!-- Pulse Matrix lights overlay at the bottom of scanning screen -->
        <div class="absolute bottom-0 left-0 right-0 h-[20%] opacity-40" style="mask-image: linear-gradient(to top, #000, transparent); -webkit-mask-image: linear-gradient(to top, #000, transparent);">
            <div class="w-full h-full flex items-center justify-center">
                <svg width="320" height="80" viewBox="0 0 320 80" class="w-full max-w-[400px] h-full" aria-hidden="true">
                    @for ($r = 0; $r < 3; $r++)
                        @for ($c = 0; $c < 10; $c++)
                            @php
                                $x = $c * (20 + 8) + 20;
                                $y = $r * (12 + 8) + 10;
                                $dur = 1.0 + (($c + $r) % 4) * 0.4;
                            @endphp
                            <rect x="{{ $x }}" y="{{ $y }}" width="20" height="12" rx="3" fill="#2c2b54" class="matrix-cell" style="--cell-dur: {{ $dur }}s" />
                        @endfor
                    @endfor
                </svg>
            </div>
        </div>
    </div>

    <!-- Script Handling Ajax & Animation Stages -->
    <script>
        const scanSteps = [
            'Analyzing DNS records...',
            'Fetching homepage content...',
            'Checking legal policies...',
            'Scanning contact details...',
            'Testing 404 handler resilience...',
            'Evaluating SEO titles & metadata...',
            'Validating accessibility rules...',
            'Aggregating certification score...'
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
            }, 600);

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(response => {
                clearInterval(stepInterval);
                bar.style.width = '100%';
                stepLabel.textContent = 'Completed!';
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