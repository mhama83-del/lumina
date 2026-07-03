<?php

namespace App\Libraries;

/**
 * Builds plain-language explanations for scores/matches (the "Why?" panel).
 */
class Explain
{
    public static function match(array $m, string $domain = 'your target'): string
    {
        $n = count($m['matched']);
        $g = $m['gap'] ? (' Gap: ' . implode(', ', $m['gap']) . '.') : '';
        return "Strong match — {$n} core skills present, score {$m['matchScore']}%, fits {$domain} direction.{$g}";
    }

    public static function readiness(array $r): string
    {
        return "Readiness {$r['score']}% — based on skill coverage ({$r['coverage']}), evidence ({$r['evidence']}), "
             . "activity ({$r['activity']}), and learning pace ({$r['pace']}).";
    }

    public static function whatif(array $w, array $skills): string
    {
        return 'Add ' . implode(' + ', $skills) . " to lift readiness from {$w['before']}% to {$w['after']}%.";
    }

    public static function shortlist(int $verified): string
    {
        return "Backed by {$verified} verified item(s); reason and evidence attached.";
    }
}
