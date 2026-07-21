<?php

namespace App\Controllers;

use App\Models\EmployerRoleModel;
use App\Services\TalentMatchService;

class Employer extends BaseController
{
    /** Role browser (reads 1,000 JD from DB, with filters). */
    public function index()
    {
        if (session('role') !== 'employer') session()->set(['role' => 'employer']);

        $filters = [
            'domain'  => trim((string) $this->request->getGet('domain')),
            'level'   => trim((string) $this->request->getGet('level')),
            'country' => trim((string) $this->request->getGet('country')),
            'sector'  => trim((string) $this->request->getGet('sector')),
            'q'       => trim((string) $this->request->getGet('q')),
        ];
        $page    = max(1, (int) $this->request->getGet('page'));
        $perPage = 24;

        $rm    = new EmployerRoleModel();
        $roles = $rm->browse($filters, $perPage, ($page - 1) * $perPage);
        $total   = $this->countRoles($filters);
        $totalJd = \Config\Database::connect()->table('employer_roles')->countAllResults();

        // fallback: if DB empty, show notice
        return view('employer/index', [
            'title'     => 'Lumina · Employer',
            'roles'     => $roles,
            'filters'   => $filters,
            'page'      => $page,
            'perPage'   => $perPage,
            'total'     => $total,
            'totalJd'   => $totalJd,
            'options'   => $this->filterOptions($filters['domain']),
            'shortlist' => session('shortlist') ?? [],
        ]);
    }

    /** Role detail + ranked candidates (TalentMatchService) + persist matches. */
    public function role($id = null)
    {
        $id = (int) $id;
        $rm   = new EmployerRoleModel();
        $role = $rm->fullRole($id);
        if (! $role) return redirect()->to(base_url('employer'));

        $ranked = $this->rankStudents($role, 12);
        $this->writeMatches($id, $ranked);

        return view('employer/role', [
            'title'     => 'Lumina · ' . $role['role_title'],
            'role'      => $role,
            'ranked'    => $ranked,
            'shortlist' => session('shortlist') ?? [],
        ]);
    }

    /** Compare 2–4 candidates against a DB role. */
    public function compare()
    {
        $roleId = (int) $this->request->getGet('role_id');
        $rm   = new EmployerRoleModel();
        $role = $rm->fullRole($roleId);
        if (! $role) return redirect()->to(base_url('employer'));

        $ids = array_slice(array_map('intval', (array) ($this->request->getGet('ids') ?? [])), 0, 4);
        $svc = new TalentMatchService();
        $db  = \Config\Database::connect();

        $statedMap = $this->statedMap($db, $ids);
        $students  = $db->table('students')
            ->select('id, name, university, programme, target_domain, evidence_text, has_resume, cgpa')
            ->whereIn('id', $ids ?: [0])->get()->getResultArray();
        $byId = []; foreach ($students as $s) { $byId[$s['id']] = $s; }

        $cands = [];
        foreach ($ids as $sid) {
            if (! isset($byId[$sid])) continue;
            $s    = $byId[$sid];
            $cand = TalentMatchService::buildStudentSignal($s, $statedMap[$sid] ?? []);
            $m    = $svc->match($cand, $role);
            $cands[] = ['id' => $sid, 'name' => $s['name'], 'university' => $s['university'],
                        'programme' => $s['programme'], 'animal' => $cand['animal']] + $m;
        }

        return view('employer/compare', [
            'title' => 'Lumina · Compare candidates',
            'role'  => $role,
            'cands' => $cands,
        ]);
    }

    /** Toggle a candidate in the session shortlist. */
    public function shortlist()
    {
        $id     = (int) $this->request->getGet('id');
        $roleId = (int) $this->request->getGet('role_id');
        $list   = session('shortlist') ?? [];
        if ($id) {
            if (in_array($id, $list, true)) $list = array_values(array_diff($list, [$id]));
            else $list[] = $id;
        }
        session()->set('shortlist', $list);
        return redirect()->to($roleId ? base_url('employer/role/' . $roleId) : base_url('employer'));
    }

    /** Full candidate brief (evidence, skills, animal, readiness) + match to a role. */
    public function candidate($id = null)
    {
        $id = (int) $id;
        $roleId = (int) $this->request->getGet('role_id');
        $db = \Config\Database::connect();
        $s  = $db->table('students')->where('id', $id)->get()->getRowArray();
        if (! $s) return redirect()->to(base_url('employer'));

        $stated = [];
        foreach ($db->table('student_skills ss')->select('sk.code')->join('skills sk', 'sk.id = ss.skill_id')
                 ->where('ss.student_id', $id)->get()->getResultArray() as $r) { $stated[] = $r['code']; }

        $svc       = new \App\Services\ScoreService();
        $explained = $svc->inferSkillsExplained($s['evidence_text'] ?? '', $stated);
        $cand      = \App\Services\TalentMatchService::buildStudentSignal($s, $stated);
        $parser    = new \App\Services\ResumeParserService();
        $animal    = $parser->animalFromEvidence($cand['skills'], $s['evidence_text'] ?? '');
        $projects  = $parser->detectProjects($s['evidence_text'] ?? '');
        $leadership= $parser->detectLeadership($s['evidence_text'] ?? '');
        $readiness = $svc->employability($cand);
        $band      = $svc->risk($readiness);

        // ---- Fasa 4: EDGE signals calon untuk HR brief (dari evidence, macam CV) ----
        $edgeCov = [];
        foreach (array_keys($explained) as $scode) {
            foreach (\App\Libraries\Edge::mapSkillToSignal((string)$scode) as $sig) {
                $edgeCov[$sig] = ($edgeCov[$sig] ?? 0) + 1;
            }
        }
        arsort($edgeCov);
        $sigDefs2 = \App\Libraries\Edge::signals();
        $edgeSignals = [];
        foreach ($edgeCov as $sig => $n) {
            if ($n < 1) continue;
            $edgeSignals[] = ['name' => $sigDefs2[$sig]['name'] ?? $sig, 'count' => $n];
        }
        $edgeQuotes = \App\Libraries\Edge::evidenceQuotes(array_keys($explained), $s['evidence_text'] ?? '');

        $role = null; $match = null;
        if ($roleId) {
            $role = (new EmployerRoleModel())->fullRole($roleId);
            if ($role) $match = (new TalentMatchService())->match($cand, $role);
        }

        return view('employer/candidate', [
            'title' => 'Lumina · ' . $s['name'],
            's' => $s, 'explained' => $explained, 'animal' => $animal,
            'projects' => $projects, 'leadership' => $leadership,
            'readiness' => $readiness, 'band' => $band, 'role' => $role, 'match' => $match,
            'edgeSignals' => $edgeSignals, 'edgeQuotes' => $edgeQuotes,
            'shortlist' => session('shortlist') ?? [],
        ]);
    }

    // ---------------- helpers ----------------

    private function rankStudents(array $role, int $limit): array
    {
        $db  = \Config\Database::connect();
        $svc = new TalentMatchService();
        $statedMap = $this->statedMap($db);

        $students = $db->table('students')
            ->select('id, name, university, programme, target_domain, evidence_text, has_resume, cgpa')
            ->get()->getResultArray();

        $out = [];
        foreach ($students as $s) {
            $cand = TalentMatchService::buildStudentSignal($s, $statedMap[$s['id']] ?? []);
            $m    = $svc->match($cand, $role);
            $out[] = ['id' => (int) $s['id'], 'name' => $s['name'], 'university' => $s['university'],
                      'programme' => $s['programme'], 'animal' => $cand['animal']] + $m;
        }
        usort($out, fn ($a, $b) => $b['match_score'] <=> $a['match_score']);
        return array_slice($out, 0, $limit);
    }

    private function writeMatches(int $roleId, array $ranked): void
    {
        try {
            $db = \Config\Database::connect();
            $db->table('candidate_role_matches')->where('employer_role_id', $roleId)->delete();
            $rows = [];
            $now = date('Y-m-d H:i:s');
            foreach ($ranked as $r) {
                $rows[] = [
                    'employer_role_id' => $roleId, 'profile_id' => $r['id'], 'reason' => $r['name'],
                    'match_score' => $r['match_score'], 'fit_label' => $r['fit_label'],
                    'skill_match_score' => $r['skill_match_score'], 'evidence_strength_score' => $r['evidence_strength_score'],
                    'learning_velocity_score' => $r['learning_velocity_score'], 'animal_fit_score' => $r['animal_fit_score'],
                    'domain_fit_score' => $r['domain_fit_score'], 'academic_fit_score' => $r['academic_fit_score'],
                    'skill_overlap_json' => json_encode($r['skill_overlap']), 'missing_skills_json' => json_encode($r['missing_skills']),
                    'explanation' => $r['explanation'], 'created_at' => $now,
                ];
            }
            if ($rows) $db->table('candidate_role_matches')->insertBatch($rows);
        } catch (\Throwable $e) {
            log_message('error', 'Lumina writeMatches: ' . $e->getMessage());
        }
    }

    private function statedMap($db, array $ids = []): array
    {
        $map = [];
        try {
            $b = $db->table('student_skills ss')->select('ss.student_id, sk.code')->join('skills sk', 'sk.id = ss.skill_id');
            if ($ids) $b->whereIn('ss.student_id', $ids);
            foreach ($b->get()->getResultArray() as $r) { $map[$r['student_id']][] = $r['code']; }
        } catch (\Throwable $e) {}
        return $map;
    }

    private function countRoles(array $f): int
    {
        $db = \Config\Database::connect();
        $b  = $db->table('employer_roles r')->join('employers e', 'e.id = r.employer_id');
        if ($f['domain'])  $b->where('r.target_domain', $f['domain']);
        if ($f['level'])   $b->where('r.role_level', $f['level']);
        if ($f['country']) $b->where('e.country', $f['country']);
        if ($f['sector'])  $b->where('e.sector', $f['sector']);
        if ($f['q']) { $b->groupStart()->like('r.role_title', $f['q'])->orLike('e.company_name', $f['q'])->orLike('r.role_family', $f['q'])->groupEnd(); }
        return $b->countAllResults();
    }

    private function filterOptions(string $domain = ''): array
    {
        $db = \Config\Database::connect();
        $col = function ($sql) use ($db) { return array_column($db->query($sql)->getResultArray(), 'k'); };
        return [
            'domains'   => $col("SELECT target_domain k FROM employer_roles GROUP BY target_domain ORDER BY COUNT(*) DESC"),
            'levels'    => ['Internship', 'Fresh Graduate', 'Graduate Trainee', 'Junior Executive', 'Junior Engineer', 'Junior Analyst'],
            'countries' => $col("SELECT DISTINCT location_country k FROM employer_roles ORDER BY k"),
            'sectors'   => $col("SELECT DISTINCT e.sector k FROM employer_roles r JOIN employers e ON e.id = r.employer_id WHERE e.sector IS NOT NULL" . ($domain !== '' ? ' AND r.target_domain = ' . $db->escape($domain) : '') . ' ORDER BY k'),
        ];
    }
}
