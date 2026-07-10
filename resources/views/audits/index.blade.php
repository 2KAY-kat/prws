<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PRWS - Scan History</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-3xl mx-auto px-6">

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Scan History</h1>
            <a href="{{ route('audits.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                New Scan
            </a>
        </div>

        @if ($audits->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-400">
                No scans yet.
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
                @foreach ($audits as $audit)
                    <a href="{{ route('audits.show', $audit) }}"
                       class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $audit->url }}</p>
                            <p class="text-xs text-gray-400">{{ $audit->created_at->diffForHumans() }}</p>
                        </div>

                        @php
                        $badgeColors = [
                            'Platinum' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-300',
                            'Gold' => 'bg-yellow-100 text-yellow-800',
                            'Silver' => 'bg-gray-200 text-gray-700',
                            'Bronze' => 'bg-orange-100 text-orange-800',
                            'None' => 'bg-red-100 text-red-700',
                        ];
                        @endphp
                        <div class="flex items-center gap-3 shrink-0 ml-4">
                            <span class="text-sm font-semibold text-gray-700">{{ $audit->score }}/100</span>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeColors[$audit->certification] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $audit->certification }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $audits->links() }}
            </div>
        @endif

    </div>
</body>
</html>