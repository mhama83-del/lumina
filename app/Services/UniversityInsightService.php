<?php

namespace App\Services;

use App\Libraries\Catalog;
use App\Services\ScoreService;
use App\Services\ResumeParserService;

/**
 * UniversityInsightService (Fasa 5)
 * Cohort intelligence extras + intervention-plan generation.
 * Field-agnostic readiness (employability) so every faculty is judged fairly.
 */
class UniversityInsightService
{
    private static array $animalMap = ['beaver'=>'Ant']; // legacy alias; 12 new ids resolve via ucfirst()
    private array $highValue = ['sql','python','data_analysis','software','cloud','entrepreneurship'];

    /** One pass over the cohort → all aggregates used by dashboard + interventions. */
    public function cohort(?string $university = null): array
    {
        $db  = \Config\Database::connect();
        $svc = new ScoreService();
        $par = new ResumeParserService();

        $statedMap = [];
        try {
            foreach ($db->table('student_skills ss')->select('ss.student_id, sk.code')
                ->join('skills sk', 'sk.id = ss.skill_id')->get()->getResultArray() as $r) {
                $statedMap[$r['student_id']][] = $r['code'];
            }
        } catch (\Throwable $e) {}

        $sq = $db->table('students')
            ->select('id, name, faculty, programme, target_domain, evidence_text, has_resume');
        if ($university !== null && $university !== '') $sq->where('university', $university);
        $students = $sq->get()->getResultArray();

        $total = count($students);
        $noResume = 0;
        $animalDist = ['Lion'=>0,'Eagle'=>0,'Wolf'=>0,'Owl'=>0,'Dolphin'=>0,'Peacock'=>0,'Elephant'=>0,'Horse'=>0,'Ant'=>0,'Cheetah'=>0,'Fox'=>0,'Octopus'=>0];
        $gapFreq = [];
        $byProg  = [];
        $needSupport = [];

        foreach ($students as $s) {
            $stated = $statedMap[$s['id']] ?? [];
            $cand   = $svc->signal($s['evidence_text'] ?? '', $stated, (int) ($s['has_resume'] ?? 0), $s['target_domain'] ?? 'Data');
            $ready  = $svc->employability($cand);
            $band   = $svc->risk($ready);

            if (! (int) ($s['has_resume'] ?? 0)) $noResume++;

            $an = $par->animalFromEvidence($cand['skills'], $s['evidence_text'] ?? '');
            $pid = $an['primary']['id'] ?? 'owl';
            $aName = self::$animalMap[$pid] ?? ucfirst($pid);
            $animalDist[$aName] = ($animalDist[$aName] ?? 0) + 1;

            $role = $this->roleForCand($cand, $s['target_domain'] ?? 'Data', $svc);
            $m    = $svc->match($cand, $role);
            foreach ($m['gap'] as $g) { $gapFreq[$g] = ($gapFreq[$g] ?? 0) + 1; }

            $prog = $s['programme'] ?: 'General';
            $byProg[$prog] ??= ['total'=>0,'atrisk'=>0,'nudge'=>0,'ontrack'=>0,'faculty'=>$s['faculty'] ?: 'General','gaps'=>[]];
            $byProg[$prog]['total']++;
            if ($band === 'At risk')      $byProg[$prog]['atrisk']++;
            elseif ($band === 'Needs a nudge') $byProg[$prog]['nudge']++;
            else                          $byProg[$prog]['ontrack']++;
            foreach ($m['gap'] as $g) { $byProg[$prog]['gaps'][$g] = ($byProg[$prog]['gaps'][$g] ?? 0) + 1; }

            if ($band === 'At risk' && count($needSupport) < 12) {
                $needSupport[] = ['id'=>(int)$s['id'],'name'=>$s['name'],'programme'=>$prog,'faculty'=>$s['faculty'] ?: 'General','readiness'=>$ready];
            }
        }

        arsort($gapFreq);
        return compact('total','noResume','animalDist','gapFreq','byProg','needSupport');
    }

    /** Extras for the dashboard. */
    public function snapshot(?string $university = null): array
    {
        $c = $this->cohort($university);
        $topGaps = [];
        foreach (array_slice($c['gapFreq'], 0, 6, true) as $code => $n) {
            $topGaps[] = ['code'=>$code,'label'=>Catalog::label($code),'count'=>$n];
        }
        $gapsByProgramme = [];
        foreach ($c['byProg'] as $prog => $d) {
            if (! $d['gaps']) continue;
            arsort($d['gaps']);
            $top = array_key_first($d['gaps']);
            $gapsByProgramme[] = ['programme'=>$prog,'code'=>$top,'gap'=>Catalog::label($top),'atrisk'=>$d['atrisk'],'total'=>$d['total']];
        }
        usort($gapsByProgramme, fn ($a,$b) => $b['atrisk'] <=> $a['atrisk']);
        $gapsByProgramme = array_slice($gapsByProgramme, 0, 8);

        return [
            'noResumePct'     => $c['total'] ? (int) round(100 * $c['noResume'] / $c['total']) : 0,
            'noResumeCount'   => $c['noResume'],
            'animalDist'      => $c['animalDist'],
            'topGaps'         => $topGaps,
            'gapsByProgramme' => $gapsByProgramme,
            'needSupport'     => $c['needSupport'],
        ];
    }

    /** Intervention plan: per programme, the highest-impact action. */
    public function interventionPlan(?string $university = null): array
    {
        $c = $this->cohort($university);
        $plan = [];
        foreach ($c['byProg'] as $prog => $d) {
            $target = $d['atrisk'] + $d['nudge'];
            if ($target === 0 || ! $d['gaps']) continue;
            arsort($d['gaps']);
            $gapCode = array_key_first($d['gaps']);
            $gapCount = $d['gaps'][$gapCode];
            $plan[] = [
                'programme'   => $prog,
                'faculty'     => $d['faculty'],
                'target'      => $target,
                'atrisk'      => $d['atrisk'],
                'skill_gap'   => Catalog::label($gapCode),
                'gap_count'   => $gapCount,
                'workshop'    => $this->workshopFor($gapCode),
                'expected'    => 'Could move up to ' . min($target, $gapCount) . ' students toward career-ready in ' . $prog . '.',
                'why'         => $gapCount . ' of ' . $d['total'] . ' students in ' . $prog . ' are missing ' . Catalog::label($gapCode)
                               . ' — the most common gap here. A targeted ' . $this->workshopFor($gapCode) . ' is the single highest-leverage action for this programme.',
            ];
        }
        usort($plan, fn ($a,$b) => $b['target'] <=> $a['target']);
        return array_slice($plan, 0, 12);
    }

    private function workshopFor(string $code): string
    {
        $m = [
            'sql'=>'SQL & Data Querying Bootcamp',
            'python'=>'Python Programming Bootcamp',
            'dashboarding'=>'Dashboarding (Power BI / Tableau) Workshop',
            'data_analysis'=>'Data Analytics Fundamentals',
            'statistics'=>'Applied Statistics Workshop',
            'machine_learning'=>'Intro to Machine Learning',
            'software'=>'Software Development Bootcamp',
            'cloud'=>'Cloud Fundamentals (AWS) Workshop',
            'api'=>'API Design Lab',
            'communication'=>'Business Communication & Presentation Workshop',
            'leadership'=>'Student Leadership Programme',
            'stakeholder_mgmt'=>'Stakeholder Management Workshop',
            'project_mgmt'=>'Project Management Essentials',
            'teamwork'=>'Teamwork & Collaboration Lab',
            'ui_ux'=>'UX/UI Design Studio',
            'figma'=>'Figma Prototyping Workshop',
            'marketing'=>'Digital Marketing Bootcamp',
            'finance'=>'Financial Literacy Workshop',
            'accounting'=>'Accounting Fundamentals',
        ];
        return $m[$code] ?? ('Skills Bootcamp: ' . Catalog::label($code));
    }

    /** Filtered student list for drill-down. */
    public function studentList(array $f): array
    {
        $db  = \Config\Database::connect();
        $svc = new ScoreService();
        $statedMap = [];
        try {
            foreach ($db->table('student_skills ss')->select('ss.student_id, sk.code')
                ->join('skills sk', 'sk.id = ss.skill_id')->get()->getResultArray() as $r) {
                $statedMap[$r['student_id']][] = $r['code'];
            }
        } catch (\Throwable $e) {}

        $q = $db->table('students')->select('id, name, university, faculty, programme, target_domain, evidence_text, has_resume');
        if (! empty($f['uni']))       $q->where('university', $f['uni']);
        if (! empty($f['programme'])) $q->where('programme', $f['programme']);
        $students = $q->get()->getResultArray();

        $out = [];
        foreach ($students as $s) {
            $stated = $statedMap[$s['id']] ?? [];
            $cand   = $svc->signal($s['evidence_text'] ?? '', $stated, (int) $s['has_resume'], $s['target_domain'] ?? 'Data');
            $ready  = $svc->employability($cand);
            $band   = $svc->risk($ready);
            if (! empty($f['band']) && $band !== $f['band']) continue;
            $role = $this->roleForCand($cand, $s['target_domain'] ?? 'Data', $svc);
            $m    = $svc->match($cand, $role);
            if (! empty($f['gap']) && ! in_array($f['gap'], $m['gap'], true)) continue;
            if (! empty($f['metric']) && ! $this->passesMetric($f['metric'], $cand, $ready, $m, $s['evidence_text'] ?? '', $s['target_domain'] ?? 'Data', $svc)) continue;
            $out[] = [
                'id' => (int) $s['id'], 'name' => $s['name'], 'university' => $s['university'],
                'programme' => $s['programme'], 'faculty' => $s['faculty'],
                'readiness' => $ready, 'band' => $band,
                'gaps' => Catalog::labels(array_slice($m['gap'], 0, 3)),
            ];
            if (count($out) >= 300) break;
        }
        usort($out, fn ($a, $b) => $a['readiness'] <=> $b['readiness']); // most at-risk first
        return $out;
    }

    /** Full student profile + why (for the drill-down detail page). */
    public function studentProfile(int $id): ?array
    {
        $db = \Config\Database::connect();
        $s = $db->table('students')->where('id', $id)->get()->getRowArray();
        if (! $s) return null;
        $stated = [];
        foreach ($db->table('student_skills ss')->select('sk.code')->join('skills sk', 'sk.id = ss.skill_id')
                 ->where('ss.student_id', $id)->get()->getResultArray() as $r) { $stated[] = $r['code']; }

        $svc = new ScoreService(); $par = new ResumeParserService();
        $explained = $svc->inferSkillsExplained($s['evidence_text'] ?? '', $stated);
        $cand      = $svc->signal($s['evidence_text'] ?? '', $stated, (int) $s['has_resume'], $s['target_domain'] ?? 'Data');
        $ready     = $svc->employability($cand);
        $band      = $svc->risk($ready);
        $role      = $this->roleForCand($cand, $s['target_domain'] ?? 'Data', $svc);
        $rd        = $svc->readiness($cand, $role);
        $m         = $svc->match($cand, $role);
        $animal    = $par->animalFromEvidence($cand['skills'], $s['evidence_text'] ?? '');
        $gapLabels = Catalog::labels($m['gap']);

        $rec = $band === 'At risk'
            ? 'Priority support: build ' . ($gapLabels[0] ?? 'core skills') . ' and add one verified project or internship.'
            : ($band === 'Needs a nudge'
                ? 'Close the gap in ' . ($gapLabels[0] ?? 'a key skill') . ' to reach On Track.'
                : 'On track — keep building evidence and apply to matched roles.');

        return [
            's' => $s, 'explained' => $explained, 'ready' => $ready, 'band' => $band,
            'rd' => $rd, 'gapLabels' => $gapLabels, 'animal' => $animal, 'rec' => $rec,
            'projects' => $par->detectProjects($s['evidence_text'] ?? ''),
            'leadership' => $par->detectLeadership($s['evidence_text'] ?? ''),
        ];
    }

    private ?array $rbd = null;
    private function rolesByDomain(): array
    {
        if ($this->rbd === null) {
            $this->rbd = [];
            foreach (Catalog::roles() as $r) { $this->rbd[$r['domain']][] = $r; }
        }
        return $this->rbd;
    }

    /** Best-fit Catalog role for a candidate WITHIN their own domain (relevant gaps). */
    private function roleForCand(array $cand, string $domain, ScoreService $svc): array
    {
        $rbd   = $this->rolesByDomain();
        $cands = $rbd[$domain] ?? ($rbd['Data'] ?? []);
        if (! $cands) return Catalog::role('data_analyst');
        if (count($cands) === 1) return $cands[0];
        $best = $cands[0]; $bestSc = -1;
        foreach ($cands as $r) {
            $sc = $svc->match($cand, $r)['matchScore'];
            if ($sc > $bestSc) { $bestSc = $sc; $best = $r; }
        }
        return $best;
    }

    /** Same attribute extraction the dashboard uses (kept in sync for exact KPI drill-down). */
    private function attrsFor(string $text, array $skills, string $domain): array
    {
        $t = strtolower($text);
        return [
            'internship'         => str_contains($t, 'internship') ? 1 : 0,
            'projects'           => min(3, substr_count($t, 'project') + substr_count($t, 'app') + substr_count($t, 'built') + substr_count($t, 'dashboard')),
            'certs'              => str_contains($t, 'cert') ? 1 : 0,
            'global'             => (str_contains($t, 'global') || str_contains($t, 'international') || str_contains($t, 'exchange')) ? 1 : 0,
            'high_value_skills'  => count(array_intersect(array_keys($skills), $this->highValue)),
            'high_income_domain' => in_array(ucfirst(strtolower($domain)), ['Data', 'Engineering'], true) ? 1 : 0,
            'entrepreneur'       => (str_contains($t, 'startup') || str_contains($t, 'business')) ? 1 : 0,
            'innovation'         => (str_contains($t, 'innovation') || str_contains($t, 'built') || str_contains($t, 'app')) ? 1 : 0,
            'leadership'         => isset($skills['leadership']) ? 1 : 0,
        ];
    }

    /** Whether a student belongs to a KPI metric bucket (mirrors University::index thresholds). */
    private function passesMetric(string $metric, array $cand, int $ready, array $m, string $evidence, string $domain, ScoreService $svc): bool
    {
        switch ($metric) {
            case 'ready':    return $ready >= 60;
            case 'transfer': return isset($cand['skills']['leadership']) || isset($cand['skills']['communication']) || isset($cand['skills']['teamwork']);
            case 'matched':  return (($m['matchScore'] ?? 0) >= 65);
        }
        $attr = $this->attrsFor($evidence, $cand['skills'], $domain);
        return match ($metric) {
            'industry'   => $svc->industryExposure($attr) >= 40,
            'highinc'    => $svc->highIncome($attr) >= 50,
            'jobcreator' => $svc->jobCreator($attr) >= 50,
            default      => true,
        };
    }

    private function roleKeyFor(string $domain): string
    {
        $d = ucfirst(strtolower($domain));
        return match (true) {
            in_array($d, ['Engineering','Software'], true) => 'backend_engineer',
            $d === 'Business'                              => 'product_exec',
            default                                        => 'data_analyst',
        };
    }
}
