<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PRWS Report - {{ $audit->url }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-3xl mx-auto px-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('audits.create') }}" class="text-indigo-600 text-sm hover:underline flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                New scan
            </a>

            <div class="flex items-center gap-3">
                <form action="{{ route('audits.rescan', $audit) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-indigo-600 text-sm hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Re-scan
                    </button>
                </form>

                <button type="button" onclick="copyReportLink(this)" class="text-gray-600 text-sm hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                    </svg>
                    <span id="copy-link-label">Copy Link</span>
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mt-4">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Score card with gauge --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mt-4 mb-8 flex items-center gap-8">

            @php
                $score = $audit->score ?? 0;
                $radius = 54;
                $circumference = 2 * M_PI * $radius;
                $offset = $circumference - ($score / 100) * $circumference;

                $gaugeColor = match (true) {
                    $score >= 90 => '#eab308', // gold
                    $score >= 75 => '#6b7280', // silver
                    $score >= 60 => '#ea580c', // bronze
                    default => '#dc2626',      // red / none
                };

                $badgeColors = [
                    'Gold' => 'bg-yellow-100 text-yellow-800',
                    'Silver' => 'bg-gray-200 text-gray-700',
                    'Bronze' => 'bg-orange-100 text-orange-800',
                    'None' => 'bg-red-100 text-red-700',
                ];
            @endphp

            <div class="relative w-32 h-32 shrink-0">
                <svg class="w-32 h-32 -rotate-90" viewBox="0 0 128 128">
                    <circle cx="64" cy="64" r="{{ $radius }}" stroke="#e5e7eb" stroke-width="10" fill="none" />
                    <circle
                        cx="64" cy="64" r="{{ $radius }}"
                        stroke="{{ $gaugeColor }}"
                        stroke-width="10"
                        fill="none"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                        style="transition: stroke-dashoffset 0.6s ease"
                    />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold text-gray-900">{{ $score }}</span>
                    <span class="text-xs text-gray-400">/ 100</span>
                </div>
            </div>

            <div class="min-w-0">
                <p class="text-gray-500 text-sm mb-2 truncate">{{ $audit->url }}</p>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold {{ $badgeColors[$audit->certification] ?? 'bg-gray-100 text-gray-600' }}">
                    @if ($audit->certification !== 'None')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 1a6 6 0 00-3.815 10.631C7.98 12.83 9 14.552 9 16.5V17a1 1 0 001 1h0a1 1 0 001-1v-.5c0-1.948 1.02-3.67 2.815-4.869A6 6 0 0010 1zM8.5 18a.5.5 0 000 1h3a.5.5 0 000-1h-3z" clip-rule="evenodd" />
                        </svg>
                    @endif
                    {{ $audit->certification }}
                </span>
            </div>
        </div>

        {{-- Category icon map --}}
        @php
            $categoryIcons = [
                'Legal' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                'Contact' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
                'Reliability' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
                'SEO' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />',
                'Accessibility' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />',
            ];
        @endphp

        <div class="space-y-6">
            @foreach ($findings->groupBy('category') as $category => $categoryFindings)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {!! $categoryIcons[$category] ?? '<circle cx="12" cy="12" r="9" stroke-width="2" />' !!}
                        </svg>
                        {{ $category }}
                    </h2>
                    <ul class="space-y-3">
                        @foreach ($categoryFindings as $finding)
                            <li class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    @if ($finding->passed)
                                        <span class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-600 shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="w-6 h-6 flex items-center justify-center rounded-full bg-red-100 text-red-600 shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $finding->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $finding->rule_id }} · {{ $finding->severity }}</p>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">
                                    {{ $finding->points_earned }}/{{ $finding->points_available }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

    </div>

    <script>
        function copyReportLink(btn) {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const label = document.getElementById('copy-link-label');
                const original = label.textContent;
                label.textContent = 'Copied!';
                setTimeout(() => { label.textContent = original; }, 1500);
            });
        }
    </script>
</body>
</html>