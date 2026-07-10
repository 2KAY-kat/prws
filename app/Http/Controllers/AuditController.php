<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Finding;
use App\Services\RuleEngine;
use App\Support\Certification;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function create()
    {
        $tierOrder = ['Bronze', 'Silver', 'Gold', 'Platinum'];

        $showcaseCards = collect($tierOrder)
            ->map(fn ($tier) => Audit::with('findings')
                ->where('certification', $tier)
                ->orderByDesc('score')
                ->latest()
                ->first())
            ->filter()   // drop tiers with no scans yet
            ->values();

        $totalScans = Audit::count();

        return view('audits.create', [
            'showcaseCards' => $showcaseCards,
            'totalScans' => $totalScans,
        ]);
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

        $audit = $this->scoreAndCertify($results, $data['url']);

        return redirect()->route('audits.show', $audit);
    }

    public function rescan(Audit $audit)
    {
        $engine = new RuleEngine($audit->url);
        $results = $engine->run();

        if (!$engine->isReachable()) {
            return redirect()
                ->route('audits.show', $audit)
                ->withErrors(['url' => $engine->getFetchErrorMessage() ?? 'Could not reach this site.']);
        }

        $newAudit = $this->scoreAndCertify($results, $audit->url);

        return redirect()->route('audits.show', $newAudit);
    }

    public function index()
    {
        $audits = Audit::latest()->paginate(15);

        return view('audits.index', ['audits' => $audits]);
    }

    public function show(Audit $audit)
    {
        return view('audits.show', [
            'audit' => $audit,
            'findings' => $audit->findings,
        ]);
    }

    /**
     * Score a rule-engine result set, determine certification, and persist
     * as a new Audit + Finding records.
     */
    protected function scoreAndCertify(array $results, string $url): Audit
    {
        $totalAvailable = array_sum(array_column($results, 'points'));
        $totalEarned = array_sum(array_column($results, 'points_earned'));
        $score = $totalAvailable > 0 ? (int) round(($totalEarned / $totalAvailable) * 100) : 0;

        $hasCritical = collect($results)->contains(fn ($r) => !$r['passed'] && $r['severity'] === 'Critical');
        $hasHigh = collect($results)->contains(fn ($r) => !$r['passed'] && $r['severity'] === 'High');

        $certification = Certification::determine($score, $hasCritical, $hasHigh);

        $audit = Audit::create([
            'url' => $url,
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

        return $audit;
    }
}