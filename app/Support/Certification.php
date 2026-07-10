<?php

namespace App\Support;

class Certification
{
    /**
     * Determine certification tier from score + finding severities.
     */
    public static function determine(int $score, bool $hasCritical, bool $hasHigh): string
    {
        return match (true) {
            $score >= 95 && !$hasCritical && !$hasHigh => 'Platinum',
            $score >= 90 && !$hasCritical => 'Gold',
            $score >= 75 && !$hasCritical => 'Silver',
            $score >= 60 && !$hasCritical => 'Bronze',
            default => 'None',
        };
    }

    /**
     * Tailwind badge classes for a given tier.
     */
    public static function badgeClasses(string $tier): string
    {
        return match ($tier) {
            'Platinum' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-300',
            'Gold' => 'bg-yellow-100 text-yellow-800',
            'Silver' => 'bg-gray-200 text-gray-700',
            'Bronze' => 'bg-orange-100 text-orange-800',
            default => 'bg-red-100 text-red-700', // None
        };
    }

    /**
     * Hex color for the SVG score gauge, keyed off score (mirrors tier thresholds).
     */
    public static function gaugeColor(int $score): string
    {
        return match (true) {
            $score >= 95 => '#0ea5e9', // platinum
            $score >= 90 => '#eab308', // gold
            $score >= 75 => '#6b7280', // silver
            $score >= 60 => '#ea580c', // bronze
            default => '#dc2626',      // none
        };
    }

    public static function cardGradient(string $tier): string
    {
        return match ($tier) {
            'Platinum' => 'bg-gradient-to-b from-slate-600 via-slate-700 to-slate-900 text-white',
            'Gold' => 'bg-gradient-to-b from-yellow-300 via-yellow-400 to-yellow-600 text-yellow-950',
            'Silver' => 'bg-gradient-to-b from-gray-200 via-gray-300 to-gray-400 text-gray-900',
            'Bronze' => 'bg-gradient-to-b from-orange-300 via-orange-400 to-orange-600 text-orange-950',
            default => 'bg-gradient-to-b from-gray-100 to-gray-200 text-gray-700',
        };
    }
}