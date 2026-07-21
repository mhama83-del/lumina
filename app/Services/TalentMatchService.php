<?php

namespace App\Services;

use App\Services\ScoreService;
use App\Services\LearningVelocityService;
use App\Services\ResumeParserService;
use App\Services\TaxonomyService;

/**
 * TalentMatchService (Fasa 4B.5)
 * Implements the Gemini "Talent Match Signal":
 *   Skill 40% + Evidence 20% + Learning Velocity 20% + Animal 10% + Domain 5% + Academic 5%
 * Reads employer_roles (with skill requirements + animal fit) from the DB.
 * Fully explainable; falls back to evidence_text/programme when candidate skills are thin.
 *
 * Fasa 3b: skill matching now gives PARTIAL credit for graph-adjacent skills —
 * if a candidate doesn't have a required skill exactly, but has another skill
 * the Lumina Graph has learned co-occurs with it (taxonomy_skills.related_skills_json),
 * that counts as half credit instead of zero. This rewards candidates whose
 * skillset is genuinely close to the role even when the exact keyword isn't there.
 */
class TalentMatchService
{
    /** interim 6-animal (candidate side) -> full 12-name set */
    private static array $animalMap = ['beaver' => 'Ant']; // legacy alias; 12 new ids resolve via ucfirst()

    /** Build a candidate signal from a students-table row. */
    public static function buildStudentSignal(array $s, array $stated = []): array
    {
        $svc  = new ScoreService();
        $cand = $svc->signal($s['evidence_text'] ?? '', $stated, (int) ($s['has_resume'] ?? 0), $s['target_domain'] ?? 'Data');

        $par = new ResumeParserService();
        $an  = $par->animalFromEvidence($cand['skills'], $s['evidence_text'] ?? '');
        $pid = $an['primary']['id'] ?? 'owl';

        $cand['animal']        = self::$animalMap[$pid] ?? ucfirst($pid); // 12-id ids map via ucfirst
        $cand['evidence_text'] = $s['evidence_text'] ?? '';
        $cand['programme']     = $s['programme'] ?? '';
        $cand['cgpa']          = (isset($s['cgpa']) && is_numeric($s['cgpa'])) ? (float) $s['cgpa'] : null;
        $cand['target_domain'] = $s['target_domain'] ?? 'Data';
        return $cand;
    }

    /** Match a candidate signal to a full DB role (from EmployerRoleModel::fullRole). */
    public function match(array $cand, array $role): array
    {
        $req = []; $pref = [];
        foreach (($role['skills'] ?? []) as $s) {
            if (($s['importance'] ?? '') === 'required')  $req[]  = $s;
            elseif (($s['importance'] ?? '') === 'preferred') $pref[] = $s;
        }
        $have = $cand['skills'] ?? [];
        $text = strtolower($cand['evidence_text'] ?? '');

        $has = function (array $s) use ($have, $text): bool {
            $code = $s['skill_code'] ?? '';
            $name = strtolower($s['skill_name'] ?? '');
            if ($code && isset($have[$code])) return true;
            if ($name && str_contains($text, $name)) return true;
            if ($code && str_contains($text, str_replace('_', ' ', $code))) return true;
            return false;
        };

        // graph-adjacency: skills the Lumina Graph has learned co-occur with
        // what this candidate already has. Not an exact match, but not
        // nothing either — a candidate strong in "aerodynamics" who is
        // missing "structural_engineering" but has related graph neighbours
        // for it deserves partial credit, not a zero.
        $expanded = [];
        if ($have) {
            try {
                $expanded = array_flip((new TaxonomyService())->expandSkills(array_keys($have)));
            } catch (\Throwable $e) {
                $expanded = [];
            }
        }
        $isGraphAdjacent = function (array $s) use ($expanded, $have): bool {
            $code = $s['skill_code'] ?? '';
            return $code !== '' && ! isset($have[$code]) && isset($expanded[$code]);
        };

        $matched = []; $partial = []; $missing = [];
        $credit  = 0.0;
        foreach ($req as $s) {
            if ($has($s))                    { $matched[] = $s['skill_name']; $credit += 1.0; }
            elseif ($isGraphAdjacent($s))     { $partial[] = $s['skill_name']; $credit += 0.5; }
            else                              { $missing[] = $s['skill_name']; }
        }
        $prefMatched = 0; $prefPartial = 0;
        foreach ($pref as $s) {
            if ($has($s)) $prefMatched++;
            elseif ($isGraphAdjacent($s)) $prefPartial++;
        }

        $skillScore = $req ? (int) round(100 * $credit / count($req)) : 60;
        $skillScore = (int) min(100, $skillScore + 4 * $prefMatched + 2 * $prefPartial);

        // keyword + domain-transfer signal (Lumina: trajectory over exact history)
        $kw = json_decode($role['keywords_for_matching_json'] ?? '[]', true) ?: [];
        $kwHits = 0;
        foreach ($kw as $k) {
            $k = strtolower(trim($k));
            if ($k !== '' && (str_contains($text, $k) || isset($have[str_replace(' ', '_', $k)]))) $kwHits++;
        }
        $skillScore = (int) min(100, $skillScore + 3 * $kwHits);
        $domainAligned = strcasecmp((string) ($cand['top_domain'] ?? $cand['target_domain'] ?? ''), (string) ($role['target_domain'] ?? '')) === 0;
        if ($domainAligned) $skillScore = max($skillScore, 48);

        $evidence = $this->evidenceStrength($cand, $text);
        $velocity = (new LearningVelocityService())->velocity($cand)['score'];
        $animal   = $this->animalFit($cand, $role);
        $domain   = $this->domainFit($cand, $role);
        $cgpa     = $this->cgpaFit($cand, $role);

        $total = (int) round(0.40 * $skillScore + 0.30 * $evidence + 0.20 * $velocity + 0.05 * $domain + 0.05 * $cgpa);
        $label = self::label($total);

        $topMatched = array_slice($matched, 0, 2);
        $explain = ($topMatched ? 'Strong on ' . implode(' & ', $topMatched) . '. ' : '')
                 . ($partial ? 'Related skills toward ' . implode(', ', array_slice($partial, 0, 2)) . ' (via Lumina Graph). ' : '')
                 . "{$skillScore}% skill match, evidence {$evidence}, velocity {$velocity}, "
                 . ($missing ? '. Gaps: ' . implode(', ', array_slice($missing, 0, 3)) . '.' : '.');

        return [
            'match_score'             => $total,
            'fit_label'               => $label,
            'skill_match_score'       => $skillScore,
            'evidence_strength_score' => $evidence,
            'learning_velocity_score' => $velocity,
            'animal_fit_score'        => $animal,
            'domain_fit_score'        => $domain,
            'academic_fit_score'      => $cgpa,
            'skill_overlap'           => $matched,
            'skill_partial_overlap'   => $partial,
            'missing_skills'          => $missing,
            'explanation'             => $explain,
        ];
    }

    public static function label(int $total): string
    {
        if ($total >= 85) return 'Strong Match';
        if ($total >= 70) return 'Good Match';
        if ($total >= 55) return 'Emerging';
        if ($total >= 40) return 'Developing';
        return 'Early-stage';
    }

    private function evidenceStrength(array $cand, string $text): int
    {
        $base = (new ScoreService())->employability($cand);
        if (preg_match('/\d/', $text)) $base += 8;
        foreach (['led', 'managed', 'built', 'launched', 'won ', 'increased', 'reduced', 'internship'] as $w) {
            if (str_contains($text, $w)) $base += 3;
        }
        return (int) min(100, $base);
    }

    private function animalFit(array $cand, array $role): int
    {
        $a = $cand['animal'] ?? null;
        if (! $a) return 30;
        $af = $role['animal'] ?? [];
        $primary   = $af['preferred_primary_animal'] ?? '';
        $secondary = $af['preferred_secondary_animal'] ?? '';
        $accept    = json_decode($af['acceptable_animals_json'] ?? '[]', true) ?: [];
        $poor      = strtolower($af['poor_fit_risk'] ?? '');

        if ($primary && strcasecmp($a, $primary) === 0) return 100;
        if ($secondary && strcasecmp($a, $secondary) === 0) return 85;
        foreach ($accept as $x) { if (strcasecmp($a, $x) === 0) return 50; }
        if ($poor && str_contains($poor, strtolower($a))) return 0;
        if ($primary && self::sameCategory($a, $primary)) return 60;
        return 40;
    }

    private static function sameCategory(string $a, string $b): bool
    {
        $cat = [
            'Lion' => 'L', 'Eagle' => 'L', 'Wolf' => 'L', 'Owl' => 'L',
            'Dolphin' => 'R', 'Peacock' => 'R', 'Elephant' => 'R', 'Horse' => 'R',
            'Ant' => 'E', 'Cheetah' => 'E', 'Fox' => 'E', 'Octopus' => 'E',
        ];
        $ca = $cat[ucfirst(strtolower($a))] ?? '?';
        $cb = $cat[ucfirst(strtolower($b))] ?? '!';
        return $ca === $cb;
    }

    private function domainFit(array $cand, array $role): int
    {
        $rd = $role['target_domain'] ?? '';
        $cd = $cand['top_domain'] ?? ($cand['target_domain'] ?? '');
        if ($rd && strcasecmp($cd, $rd) === 0) return 100;
        $progs = json_decode($role['suitable_programmes_json'] ?? '[]', true) ?: [];
        $p = strtolower($cand['programme'] ?? '');
        if ($p) {
            foreach ($progs as $pr) {
                $pr = strtolower($pr);
                if (str_contains($p, $pr) || str_contains($pr, $p)) return 100;
            }
        }
        return 40;
    }

    private function cgpaFit(array $cand, array $role): int
    {
        $min = $role['minimum_cgpa_category'] ?? 'N/A';
        if ($min === 'N/A' || ! is_numeric($min)) return 100;
        $c = $cand['cgpa'] ?? null;
        if ($c === null) return 70;
        return $c >= (float) $min ? 100 : (int) max(0, round(100 * ($c / (float) $min)));
    }
}
