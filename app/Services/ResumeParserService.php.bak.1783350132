<?php

namespace App\Services;

use App\Libraries\WorkAnimal;

/**
 * ResumeParserService (Fasa 2)
 * Membina "extracted profile" yang lebih kaya di atas ScoreService:
 * projects, leadership, career cluster, field of study, dan Work Animal
 * (interim 6-animal; Fasa 6 naik taraf ke AnimalInferenceService 12-animal).
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
            'Engineering' => 'Engineering & Applied Sciences',
            'Design'      => 'Product & Experience Design',
            'Business'    => 'Business, Growth & Operations',
        ][$domain] ?? 'Business, Growth & Operations';
    }

    /**
     * Deterministic "field of study" detector.
     *
     * Reads the resume's stated degree/programme line — e.g. "Bachelor of
     * Chemical Engineering", "B.Eng (Hons) Mechanical Engineering", "Diploma
     * in Electrical Engineering", or Malay "Ijazah Sarjana Muda Kejuruteraan
     * Kimia" — and maps it to a domain + a specific Catalog role.
     *
     * This is intentionally checked BEFORE loose skill-keyword guessing:
     * what a candidate actually studied is a stronger, more trustworthy
     * signal than generic soft-skill words (communication, teamwork, etc.)
     * that show up in almost every resume regardless of field.
     *
     * Returns ['raw' => original matched text|null, 'domain' => string|null, 'role' => Catalog key|null].
     */
    public function extractFieldOfStudy(string $text): array
    {
        $patterns = [
            '/bachelor(?:\'s)?\s+(?:degree\s+)?(?:of|in)\s+([a-zA-Z][a-zA-Z\s&\/\-\(\)]{2,70})/u',
            '/b\.?\s?(?:eng|sc|a|tech|comp\.?sc)\.?\s*(?:\(hons\)\s*)?(?:in\s+)?([a-zA-Z][a-zA-Z\s&\/\-\(\)]{2,70})/u',
            '/master(?:\'s)?\s+(?:degree\s+)?(?:of|in)\s+([a-zA-Z][a-zA-Z\s&\/\-\(\)]{2,70})/u',
            '/diploma\s+(?:in|of)\s+([a-zA-Z][a-zA-Z\s&\/\-\(\)]{2,70})/u',
            '/ijazah\s+sarjana\s+muda\s+([a-zA-Z][a-zA-Z\s&\/\-\(\)]{2,70})/iu',
            '/(?:field of study|major|programme|program|course)\s*[:\-]\s*([a-zA-Z][a-zA-Z\s&\/\-\(\)]{2,70})/u',
        ];

        $raw = null;
        foreach ($patterns as $p) {
            if (preg_match($p, $text, $m)) {
                $candidate = trim(preg_split('/[\n\r\.,;]/', $m[1])[0] ?? '');
                $candidate = trim($candidate, " \t-–—");
                if ($candidate !== '' && mb_strlen($candidate) <= 70) { $raw = $candidate; break; }
            }
        }
        if (! $raw) return ['raw' => null, 'domain' => null, 'role' => null];

        $f = strtolower($raw);

        // field-keyword => [domain, specific Catalog role key]. Order matters —
        // more specific / multi-word phrases are checked first.
        $map = [
            'computer science'        => ['Engineering', 'software_engineer'],
            'computer engineering'    => ['Engineering', 'software_engineer'],
            'information technology'  => ['Data', 'data_analyst'],
            'information system'     => ['Data', 'data_analyst'],
            'data science'            => ['Data', 'ml_engineer'],
            'business administration' => ['Business', 'product_exec'],
            'business management'     => ['Business', 'product_exec'],
            'mass communication'      => ['Business', 'content_creator'],
            'chemical'                => ['Engineering', 'process_engineer'],
            'petrochemical'           => ['Engineering', 'process_engineer'],
            'mechatronic'             => ['Engineering', 'mechanical_engineer'],
            'mechanical'              => ['Engineering', 'mechanical_engineer'],
            'aerospace'               => ['Engineering', 'aerospace_engineer'],
            'aeronautical'            => ['Engineering', 'aerospace_engineer'],
            'electrical'              => ['Engineering', 'electrical_engineer'],
            'electronic'              => ['Engineering', 'electrical_engineer'],
            'civil'                   => ['Engineering', 'civil_engineer'],
            'structural'              => ['Engineering', 'civil_engineer'],
            'robotics'                => ['Engineering', 'mechanical_engineer'],
            'software'                => ['Engineering', 'software_engineer'],
            'statistics'              => ['Data', 'data_analyst'],
            'mathematics'             => ['Data', 'data_analyst'],
            'actuarial'               => ['Data', 'data_analyst'],
            'design'                  => ['Design', 'ux_designer'],
            'multimedia'              => ['Design', 'ux_designer'],
            'creative'                => ['Design', 'ux_designer'],
            'accounting'              => ['Business', 'accountant'],
            'finance'                 => ['Business', 'accountant'],
            'economics'               => ['Business', 'accountant'],
            'marketing'               => ['Business', 'marketing_exec'],
            'management'              => ['Business', 'product_exec'],
            'communication'           => ['Business', 'content_creator'],
        ];

        foreach ($map as $kw => $pair) {
            if (str_contains($f, $kw)) {
                return ['raw' => $raw, 'domain' => $pair[0], 'role' => $pair[1]];
            }
        }
        return ['raw' => $raw, 'domain' => null, 'role' => null];
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
