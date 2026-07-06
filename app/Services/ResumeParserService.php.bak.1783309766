<?php

namespace App\Services;

use App\Libraries\WorkAnimal;

/**
 * ResumeParserService (Fasa 2)
 * Membina "extracted profile" yang lebih kaya di atas ScoreService:
 * projects, leadership, career cluster, dan Work Animal (interim 6-animal;
 * Fasa 6 naik taraf ke AnimalInferenceService 12-animal).
 * Deterministik & explainable — tiada panggilan AI luar.
 */
class ResumeParserService
{
    /** Klausa yang menandakan projek. */
    public function detectProjects(string $text): array
    {
        return $this->pick($text, ['project','app','built','build','developed','system','website','dashboard','prototype','platform']);
    }

    /** Klausa yang menandakan kepimpinan / aktiviti pimpinan. */
    public function detectLeadership(string $text): array
    {
        return $this->pick($text, ['treasurer','president','captain','head ','led ','lead ','chair','director','manage','mentor','coordinat','organis','founder']);
    }

    /** Pecah teks jadi klausa; pulang yang mengandungi mana-mana kata kunci. */
    private function pick(string $text, array $kw): array
    {
        $clauses = array_filter(array_map('trim', preg_split('/[;.\n]+/', $text)));
        $out = [];
        foreach ($clauses as $c) {
            $lc = strtolower($c);
            foreach ($kw as $k) {
                if (str_contains($lc, $k)) { $out[] = $this->tidy($c); break; }
            }
        }
        return array_values(array_unique(array_slice($out, 0, 6)));
    }

    private function tidy(string $s): string
    {
        $s = trim($s);
        return mb_strlen($s) > 120 ? mb_substr($s, 0, 117) . '…' : $s;
    }

    /** Career cluster daripada domain. */
    public function careerCluster(string $domain): string
    {
        return [
            'Data'        => 'Data, Analytics & AI',
            'Engineering' => 'Software & Cloud Engineering',
            'Design'      => 'Product & Experience Design',
            'Business'    => 'Business, Growth & Operations',
        ][$domain] ?? 'Business, Growth & Operations';
    }

    /**
     * Work Animal daripada bukti (interim, 6 haiwan sedia ada).
     * Pulang primary / secondary / growth + confidence + safe line.
     */
    public function animalFromEvidence(array $skills, string $text): array
    {
        $t     = strtolower($text);
        $codes = array_keys($skills);
        $sig   = ['owl'=>0,'beaver'=>0,'eagle'=>0,'fox'=>0,'dolphin'=>0,'lion'=>0];

        $kwmap = [
            'owl'     => ['analy','data','research','statistic','sql','python','dashboard','model','insight'],
            'beaver'  => ['built','build','system','process','backend','organis','coordinat','detail','api','cloud','docker'],
            'eagle'   => ['found','startup','vision','architect','senior','launch','strategy','innovat'],
            'fox'     => ['business','market','product','adapt','growth','sales','campaign','content'],
            'dolphin' => ['volunteer','community','team','communicat','customer','mentor','help','collaborat'],
            'lion'    => ['president','captain','led','head','manage','treasurer','director','chair'],
        ];
        foreach ($kwmap as $an => $kws) {
            foreach ($kws as $k) { if (str_contains($t, $k)) $sig[$an] += 1; }
        }
        foreach ($codes as $c) {
            if (in_array($c, ['data_analysis','sql','statistics','machine_learning','research'], true)) $sig['owl'] += 1;
            if (in_array($c, ['software','cloud','api','java'], true))                                  $sig['beaver'] += 1;
            if (in_array($c, ['entrepreneurship','innovation'], true))                                  $sig['eagle'] += 1;
            if (in_array($c, ['marketing','sales','content','social_media'], true))                     $sig['fox'] += 1;
            if (in_array($c, ['communication','teamwork','community','customer_service'], true))        $sig['dolphin'] += 1;
            if (in_array($c, ['leadership','stakeholder_mgmt','project_mgmt','budgeting'], true))        $sig['lion'] += 1;
        }

        arsort($sig);
        $order = array_keys($sig);
        $total = max(1, array_sum($sig));
        $primaryId   = $order[0];
        $secondaryId = $order[1] ?? 'fox';
        $growthId    = end($order); // paling lemah = ruang tumbuh

        $A = fn ($id) => ['id' => $id] + WorkAnimal::get($id); // + label, traits, domains

        $conf = (int) round(100 * $sig[$primaryId] / $total);
        $conf = max(45, min(92, $conf ?: 55));

        $pl = WorkAnimal::get($primaryId)['label'];
        $sl = WorkAnimal::get($secondaryId)['label'];
        $line = "Your resume shows strong {$pl} signals, with a secondary {$sl} streak. "
              . "This reflects how you work now — not a fixed label.";

        return [
            'primary'    => $A($primaryId),
            'secondary'  => $A($secondaryId),
            'growth'     => $A($growthId),
            'confidence' => $conf,
            'line'       => $line,
        ];
    }
}
