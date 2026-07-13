<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $fillable = ['url', 'score', 'certification', 'rules_count'];
    public function findings() { return $this->hasMany(Finding::class); }

    public function categoryScores(): array
{
    return $this->findings
        ->groupBy('category')
        ->map(function ($group) {
            $available = $group->sum('points_available');
            $earned = $group->sum('points_earned');
            return $available > 0 ? (int) round(($earned / $available) * 100) : 0;
        })
        ->toArray();
}
}


