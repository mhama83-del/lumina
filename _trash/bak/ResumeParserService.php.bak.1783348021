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
     * Work Animal (Fasa 6) — delegates to the full 12-archetype engine.
     * Kept here for backward compatibility with existing callers.
     */
    public function animalFromEvidence(array $skills, string $text): array
    {
        return (new \App\Services\AnimalInferenceService())->infer($skills, $text);
    }
}
