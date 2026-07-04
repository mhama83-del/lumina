<?php

namespace App\Services;

use App\Libraries\Catalog;
use App\Services\ScoreService;
use App\Services\LearningVelocityService;
use App\Services\ResumeParserService;

/**
 * EmployerComparisonService (Fasa 4)
 * Side-by-side comparison bagi 2–4 calon terhadap satu role.
 * Semua metrik deterministik & explainable.
 */
class EmployerComparisonService
{
    public function compare(array $role, array $ids): array
    {
        $ids = array_slice(array_values(array_unique(array_map('intval', $ids))), 0, 4);
        if (count($ids) < 1) return [];

        $db  = \Config\Database::connect();
        $svc = new ScoreService();
        $vel = new LearningVelocityService();
        $par = new ResumeParserService();

        // stated skills per student
        $statedMap = [];
        try {
            $rows = $db->table('student_skills ss')->select('ss.student_id, sk.code')
                ->join('skills sk', 'sk.id = ss.skill_id')->whereIn('ss.student_id', $ids)
                ->get()->getResultArray();
            foreach ($rows as $r) { $statedMap[$r['student_id']][] = $r['code']; }
        } catch (\Throwable $e) {}

        $students = $db->table('students')
            ->select('id, name, university, programme, target_domain, evidence_text, has_resume')
            ->whereIn('id', $ids)->get()->getResultArray();

        // preserve the selected order
        $byId = []; foreach ($students as $s) { $byId[$s['id']] = $s; }

        $out = [];
        foreach ($ids as $id) {
            if (! isset($byId[$id])) continue;
            $s      = $byId[$id];
            $stated = $statedMap[$id] ?? [];
            $cand   = $svc->signal($s['evidence_text'] ?? '', $stated, (int) ($s['has_resume'] ?? 0), $s['target_domain'] ?? 'Data');
            $codes  = array_keys($cand['skills']);

            $m   = $svc->match($cand, $role);
            $r   = $svc->readiness($cand, $role);
            $v   = $vel->velocity($cand);
            $an  = $par->animalFromEvidence($cand['skills'], $s['evidence_text'] ?? '');

            $techFit = (int) ($r['coverage'] ?? 0);
            $leadFit = min(100, 22 * count(array_intersect($codes, ['leadership','stakeholder_mgmt','project_mgmt','communication','teamwork'])));
            $evidence = $svc->employability($cand);
            $risk = $svc->risk($r['score']);
            $bestRole = Catalog::role($this->bestRoleKey($cand, $svc));

            $out[] = [
                'id'         => $id,
                'name'       => $s['name'],
                'university' => $s['university'],
                'programme'  => $s['programme'],
                'match'      => $m['matchScore'],
                'label'      => $m['label'],
                'readiness'  => $r['score'],
                'velocity'   => $v['score'],
                'velBand'    => $v['band'],
                'animal'     => $an['primary']['label'],
                'techFit'    => $techFit,
                'leadFit'    => $leadFit,
                'evidence'   => $evidence,
                'missing'    => Catalog::labels($m['gap']),
                'risk'       => $risk,
                'bestRole'   => $bestRole['title'],
                'why'        => $this->whyHire($m, $r, $v, $role),
            ];
        }
        return $out;
    }

    private function whyHire(array $m, array $r, array $v, array $role): string
    {
        $top = array_slice($m['matched'], 0, 2);
        $tl  = $top ? Catalog::labels($top) : [];
        $strong = $tl ? ('Strong on ' . implode(' & ', $tl) . '. ') : '';
        return $strong . $r['score'] . '% ready for ' . $role['title']
             . ', ' . strtolower($v['band']) . ' learning velocity'
             . ($m['gap'] ? '. Gap: ' . implode(', ', Catalog::labels($m['gap'])) . '.' : '.');
    }

    private function bestRoleKey(array $cand, ScoreService $svc): string
    {
        $bestKey = 'data_analyst'; $bestScore = -1;
        foreach (Catalog::roles() as $key => $role) {
            $sc = $svc->match($cand, $role)['matchScore'];
            if ($sc > $bestScore) { $bestScore = $sc; $bestKey = $key; }
        }
        return $bestKey;
    }
}
