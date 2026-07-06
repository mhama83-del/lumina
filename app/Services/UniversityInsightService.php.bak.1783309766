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
    private static array $animalMap = ['owl'=>'Owl','fox'=>'Fox','eagle'=>'Eagle','dolphin'=>'Dolphin','beaver'=>'Ant','lion'=>'Lion'];

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
            $aName = self::$animalMap[$an['primary']['id'] ?? 'owl'] ?? 'Owl';
            $animalDist[$aName] = ($animalDist[$aName] ?? 0) + 1;

            $role = Catalog::role($this->roleKeyFor($s['target_domain'] ?? 'Data'));
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
                $needSupport[] = ['name'=>$s['name'],'programme'=>$prog,'faculty'=>$s['faculty'] ?: 'General','readiness'=>$ready];
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
            $topGaps[] = ['label'=>Catalog::label($code),'count'=>$n];
        }
        $gapsByProgramme = [];
        foreach ($c['byProg'] as $prog => $d) {
            if (! $d['gaps']) continue;
            arsort($d['gaps']);
            $top = array_key_first($d['gaps']);
            $gapsByProgramme[] = ['programme'=>$prog,'gap'=>Catalog::label($top),'atrisk'=>$d['atrisk'],'total'=>$d['total']];
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
            $plan[] = [
                'programme'   => $prog,
                'faculty'     => $d['faculty'],
                'target'      => $target,
                'atrisk'      => $d['atrisk'],
                'skill_gap'   => Catalog::label($gapCode),
                'workshop'    => $this->workshopFor($gapCode),
                'expected'    => 'Could move up to ' . min($target, $d['gaps'][$gapCode]) . ' students toward career-ready in ' . $prog . '.',
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
