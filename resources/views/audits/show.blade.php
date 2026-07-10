<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PRWS Report - {{ $audit->url }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-3xl mx-auto px-6">

        <a href="{{ route('audits.create') }}" class="text-indigo-600 text-sm hover:underline">&larr; New scan</a>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mt-4 mb-8">
            <p class="text-gray-500 text-sm mb-1 truncate">{{ $audit->url }}</p>
            <div class="flex items-end gap-4">
                <span class="text-5xl font-bold text-gray-900">{{ $audit->score }}<span class="text-2xl text-gray-400">/100</span></span>

                @php
                    $badgeColors = [
                        'Gold' => 'bg-yellow-100 text-yellow-800',
                        'Silver' => 'bg-gray-200 text-gray-700',
                        'Bronze' => 'bg-orange-100 text-orange-800',
                        'None' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badgeColors[$audit->certification] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $audit->certification }}
                </span>
            </div>
        </div>

        <div class="space-y-6">
            @foreach ($findings->groupBy('category') as $category => $categoryFindings)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">{{ $category }}</h2>
                    <ul class="space-y-3">
                        @foreach ($categoryFindings as $finding)
                            <li class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    @if ($finding->passed)
                                        <span class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-600 text-sm">✓</span>
                                    @else
                                        <span class="w-6 h-6 flex items-center justify-center rounded-full bg-red-100 text-red-600 text-sm">✕</span>
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
</body>
</html>