<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Finding;
use App\Services\RuleEngine;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function create()
    {
        return view('audits.create');
    }

   public function store(Request $request)
{
    $data = $request->validate(['url' => 'required|url']);

    $engine = new RuleEngine($data['url']);
    $results = $engine->run();

    if (!$engine->isReachable()) {
        return back()
            ->withInput()
            ->withErrors(['url' => $engine->getFetchErrorMessage() ?? 'Could not reach this site.']);
    }

        $totalAvailable = array_sum(array_column($results, 'points'));
        $totalEarned = array_sum(array_column($results, 'points_earned'));
        $score = $totalAvailable > 0 ? round(($totalEarned / $totalAvailable) * 100) : 0;

        $hasCritical = collect($results)->contains(fn ($r) => !$r['passed'] && $r['severity'] === 'Critical');
        $hasHigh = collect($results)->contains(fn ($r) => !$r['passed'] && $r['severity'] === 'High');

        $certification = match (true) {
            $score >= 95 && !$hasCritical && !$hasHigh => 'Platinum',
            $score >= 90 && !$hasCritical => 'Gold',
            $score >= 75 && !$hasCritical => 'Silver',
            $score >= 60 && !$hasCritical => 'Bronze',
            default => 'None',
        };

        $audit = Audit::create([
            'url' => $data['url'],
            'score' => $score,
            'certification' => $certification,
        ]);

        foreach ($results as $r) {
            Finding::create([
                'audit_id' => $audit->id,
                'rule_id' => $r['rule_id'],
                'name' => $r['name'],
                'category' => $r['category'],
                'severity' => $r['severity'],
                'points_available' => $r['points'],
                'points_earned' => $r['points_earned'],
                'passed' => $r['passed'],
            ]);
        }

        return redirect()->route('audits.show', $audit);
    }

    public function show(Audit $audit)
    {
        return view('audits.show', [
            'audit' => $audit,
            'findings' => $audit->findings,
        ]);
    }

    public function index()
    {
        $audits = Audit::latest()->paginate(15);

        return view('audits.index', ['audits' => $audits]);
    }

    public function rescan(Audit $audit)
{
    $engine = new RuleEngine($audit->url);
    $results = $engine->run();

    if (!$engine->isReachable()) {
        return back()->withErrors(['url' => $engine->getFetchErrorMessage() ?? 'Could not reach this site.']);
    }

    $totalAvailable = array_sum(array_column($results, 'points'));
    $totalEarned = array_sum(array_column($results, 'points_earned'));
    $score = $totalAvailable > 0 ? round(($totalEarned / $totalAvailable) * 100) : 0;

    $hasCritical = collect($results)->contains(fn ($r) => !$r['passed'] && $r['severity'] === 'Critical');
    $certification = match (true) {
        $score >= 90 && !$hasCritical => 'Gold',
        $score >= 75 && !$hasCritical => 'Silver',
        $score >= 60 && !$hasCritical => 'Bronze',
        default => 'None',
    };

    $newAudit = Audit::create([
        'url' => $audit->url,
        'score' => $score,
        'certification' => $certification,
    ]);

    foreach ($results as $r) {
        Finding::create([
            'audit_id' => $newAudit->id,
            'rule_id' => $r['rule_id'],
            'name' => $r['name'],
            'category' => $r['category'],
            'severity' => $r['severity'],
            'points_available' => $r['points'],
            'points_earned' => $r['points_earned'],
            'passed' => $r['passed'],
        ]);
    }

    return redirect()->route('audits.show', $newAudit);
}
}

