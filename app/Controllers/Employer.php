<?php

namespace App\Controllers;

use App\Libraries\Catalog;
use App\Libraries\Explain;
use App\Services\ScoreService;

class Employer extends BaseController
{
    public function index()
    {
        if (session('role') !== 'employer') session()->set(['role' => 'employer']);

        $roles = Catalog::roles();
        $key   = (string) ($this->request->getGet('role') ?? '');
        $role  = $roles[$key] ?? array_values($roles)[0];

        $ranked = $this->rankCandidates($role);

        return view('employer/index', [
            'title'    => 'Lumina · Employer',
            'roles'    => $roles,
            'selected' => $role,
            'ranked'   => $ranked,
        ]);
    }

    /** Rank all students in the DB against a role. */
    private function rankCandidates(array $role): array
    {
        $db  = \Config\Database::connect();
        $svc = new ScoreService();

        // Stated skills per student (if any)
        $statedMap = [];
        try {
            $rows = $db->table('student_skills ss')
                ->select('ss.student_id, sk.code')
                ->join('skills sk', 'sk.id = ss.skill_id')
                ->get()->getResultArray();
            foreach ($rows as $r) { $statedMap[$r['student_id']][] = $r['code']; }
        } catch (\Throwable $e) { /* table may be empty */ }

        $students = $db->table('students')
            ->select('id, name, university, programme, target_domain, evidence_text, has_resume')
            ->get()->getResultArray();

        $out = [];
        foreach ($students as $s) {
            $stated = $statedMap[$s['id']] ?? [];
            $cand   = $svc->signal($s['evidence_text'] ?? '', $stated, (int) ($s['has_resume'] ?? 0), $s['target_domain'] ?? 'Data');
            $m      = $svc->match($cand, $role);

            $out[] = [
                'name'       => $s['name'],
                'university' => $s['university'],
                'programme'  => $s['programme'],
                'match'      => $m['matchScore'],
                'label'      => $m['label'],
                'matched'    => Catalog::labels($m['matched']),
                'gap'        => Catalog::labels($m['gap']),
                'evidence'   => $this->firstEvidence($s['evidence_text'] ?? ''),
                'reason'     => Explain::match($m, $role['domain']),
                'questions'  => $this->interviewQs($role, $m['matched'], $m['gap']),
            ];
        }
        usort($out, fn ($a, $b) => $b['match'] <=> $a['match']);
        return $out;
    }

    private function interviewQs(array $role, array $matched, array $gap): array
    {
        $top = $matched[0] ?? null;
        return [
            $top ? 'Tell me about a time you used ' . Catalog::label($top) . '.' : 'Tell me about a project you are proud of.',
            ! empty($gap) ? 'How would you get up to speed on ' . Catalog::label($gap[0]) . '?' : 'What would you improve first in this role?',
            'Walk me through a ' . $role['domain'] . ' project and your exact role in it.',
        ];
    }

    private function firstEvidence(string $text): string
    {
        $parts = array_filter(array_map('trim', preg_split('/[;.]/', $text)));
        return $parts[0] ?? '—';
    }
}
