<?php

namespace App\Controllers;

use App\Libraries\Catalog;
use App\Models\EmployerRoleModel;
use App\Services\ScoreService;
use App\Services\ResumeParserService;
use App\Services\RecommendationService;
use App\Services\NoResumeProfileBuilderService;
use App\Services\AnimalInferenceService;
use App\Services\TalentMatchService;
use App\Services\UniversityInsightService;

/**
 * Api (Fasa 6.2) — headless JSON endpoints for Lumina.
 * Deterministic, explainable; reuses the same services as the UI.
 */
class Api extends BaseController
{
    private function json(array $data): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setHeader('Access-Control-Allow-Origin', '*')->setJSON($data);
    }

    /** POST/GET api/analyze-resume  (resume_text) */
    public function analyzeResume()
    {
        $text = trim((string) ($this->request->getPost('resume_text') ?? $this->request->getGet('resume_text') ?? ''));
        if ($text === '') return $this->json(['error' => 'resume_text required']);

        $svc       = new ScoreService();
        $explained = $svc->inferSkillsExplained($text, []);
        $domain    = $this->guessDomain(array_keys($explained));
        $cand      = $svc->signal($text, [], 0, $domain);

        $animal = (new AnimalInferenceService())->infer($cand['skills'], $text);
        $parser = new ResumeParserService();
        $rec    = new RecommendationService();

        [$bestKey, $bestRole] = $this->bestCatalogRole($cand, $svc);
        $bestM     = $svc->match($cand, $bestRole);
        $readiness = $svc->readiness($cand, $bestRole);
        $band      = $svc->risk($readiness['score']);

        $matches = [];
        foreach (Catalog::roles() as $role) {
            $m = $svc->match($cand, $role);
            $matches[] = ['title' => $role['title'], 'company' => $role['company'], 'match' => $m['matchScore'], 'label' => $m['label']];
        }
        usort($matches, fn ($a, $b) => $b['match'] <=> $a['match']);

        return $this->json([
            'target_domain'   => $domain,
            'career_cluster'  => $parser->careerCluster($domain),
            'readiness'       => $readiness['score'],
            'employability_band' => $band,
            'work_animal'     => ['primary' => $animal['primary'], 'secondary' => $animal['secondary'], 'growth' => $animal['growth'], 'confidence' => $animal['confidence'], 'explanation' => $animal['explanation']],
            'skills_detected' => array_map(fn ($c, $s) => ['skill' => Catalog::label($c), 'source' => $s['source']], array_keys($explained), $explained),
            'projects'        => $parser->detectProjects($text),
            'leadership'      => $parser->detectLeadership($text),
            'top_matches'     => array_slice($matches, 0, 5),
            'feedback'        => $rec->feedback($text, $cand['skills'], $readiness['score'], $parser->detectProjects($text), $parser->detectLeadership($text)),
            'next_best_action'=> $rec->nextAction($band, $bestM['gap'], $bestM['matched']),
        ]);
    }

    /** POST/GET api/build-profile (programme, interest, tools, activities, leadership, projects, cgpa) */
    public function buildProfile()
    {
        $g = fn ($k) => trim((string) ($this->request->getPost($k) ?? $this->request->getGet($k) ?? ''));
        $interest = $g('interest') ?: 'Data';
        $parts = array_filter([
            $g('programme') ? 'Studying ' . $g('programme') . '.' : '',
            $g('leadership') ? $g('leadership') . '.' : '',
            $g('activities') ? 'Active in ' . $g('activities') . '.' : '',
            $g('projects') ? $g('projects') . '.' : '',
            $g('tools') ? 'Tools: ' . $g('tools') . '.' : '',
            'Interested in ' . $interest . '.',
        ]);
        $text = trim(implode(' ', $parts));

        $svc    = new ScoreService();
        $domain = $this->normDomain($interest);
        $cand   = $svc->signal($text, [], 0, $domain);
        $animal = (new AnimalInferenceService())->infer($cand['skills'], $text);

        [$k, $bestRole] = $this->bestCatalogRole($cand, $svc);
        $bestM     = $svc->match($cand, $bestRole);
        $readiness = $svc->readiness($cand, $bestRole);
        $band      = $svc->risk($readiness['score']);

        $nrb = new NoResumeProfileBuilderService();
        $rec = new RecommendationService();

        return $this->json([
            'target_domain'  => $domain,
            'readiness_baseline' => $readiness['score'],
            'employability_band' => $band,
            'suggested_work_animal' => ['primary' => $animal['primary'], 'secondary' => $animal['secondary'], 'growth' => $animal['growth'], 'confidence' => $animal['confidence']],
            'potential_skills' => array_map(fn ($c) => Catalog::label($c), array_keys($cand['skills'])),
            'suggested_resume_draft' => $nrb->resumeDraft(['name' => 'You', 'programme' => $g('programme'), 'stage' => '19-22', 'cgpa' => $g('cgpa'), 'activities' => $g('activities'), 'leadership' => $g('leadership'), 'projects' => $g('projects'), 'tools' => $g('tools'), 'interest' => $interest], array_keys($cand['skills']), $domain),
            'first_project' => $nrb->firstProject($domain, $bestM['gap']),
            'recommended_activities' => $nrb->activities($domain),
            'recommended_courses' => $rec->microCourses($bestM['gap']),
            'next_best_action' => $rec->nextAction($band, $bestM['gap'], $bestM['matched']),
        ]);
    }

    /** GET api/match-candidates?role_id=..&limit=.. */
    public function matchCandidates()
    {
        $roleId = (int) $this->request->getGet('role_id');
        $limit  = min(50, max(1, (int) ($this->request->getGet('limit') ?: 10)));
        $role   = (new EmployerRoleModel())->fullRole($roleId);
        if (! $role) return $this->json(['error' => 'role not found']);

        $out = $this->rankStudents($role, $limit);
        return $this->json(['role' => ['id' => $role['id'], 'title' => $role['role_title'], 'company' => $role['company_name'], 'domain' => $role['target_domain']], 'candidates' => $out]);
    }

    /** GET api/compare-candidates?role_id=..&ids[]=.. */
    public function compareCandidates()
    {
        $roleId = (int) $this->request->getGet('role_id');
        $ids    = array_slice(array_map('intval', (array) ($this->request->getGet('ids') ?? [])), 0, 4);
        $role   = (new EmployerRoleModel())->fullRole($roleId);
        if (! $role) return $this->json(['error' => 'role not found']);

        $db  = \Config\Database::connect();
        $svc = new TalentMatchService();
        $stated = [];
        foreach ($db->table('student_skills ss')->select('ss.student_id, sk.code')->join('skills sk', 'sk.id = ss.skill_id')->whereIn('ss.student_id', $ids ?: [0])->get()->getResultArray() as $r) { $stated[$r['student_id']][] = $r['code']; }
        $students = $db->table('students')->select('id, name, university, programme, target_domain, evidence_text, has_resume, cgpa')->whereIn('id', $ids ?: [0])->get()->getResultArray();

        $out = [];
        foreach ($students as $s) {
            $cand = TalentMatchService::buildStudentSignal($s, $stated[$s['id']] ?? []);
            $m = $svc->match($cand, $role);
            $out[] = ['id' => (int) $s['id'], 'name' => $s['name'], 'animal' => $cand['animal']] + $m;
        }
        usort($out, fn ($a, $b) => $b['match_score'] <=> $a['match_score']);
        return $this->json(['role' => ['id' => $role['id'], 'title' => $role['role_title']], 'candidates' => $out]);
    }

    /** GET api/cohort-insight?uni=.. */
    public function cohortInsight()
    {
        $uni = trim((string) $this->request->getGet('uni'));
        $snap = (new UniversityInsightService())->snapshot($uni !== '' ? $uni : null);
        return $this->json(['university' => $uni ?: 'All', 'snapshot' => $snap]);
    }

    // ---------------- helpers ----------------

    private function rankStudents(array $role, int $limit): array
    {
        $db  = \Config\Database::connect();
        $svc = new TalentMatchService();
        $stated = [];
        foreach ($db->table('student_skills ss')->select('ss.student_id, sk.code')->join('skills sk', 'sk.id = ss.skill_id')->get()->getResultArray() as $r) { $stated[$r['student_id']][] = $r['code']; }
        $students = $db->table('students')->select('id, name, university, programme, target_domain, evidence_text, has_resume, cgpa')->get()->getResultArray();
        $out = [];
        foreach ($students as $s) {
            $cand = TalentMatchService::buildStudentSignal($s, $stated[$s['id']] ?? []);
            $m = $svc->match($cand, $role);
            $out[] = ['id' => (int) $s['id'], 'name' => $s['name'], 'university' => $s['university'], 'programme' => $s['programme'], 'animal' => $cand['animal'], 'match_score' => $m['match_score'], 'fit_label' => $m['fit_label'], 'missing_skills' => $m['missing_skills']];
        }
        usort($out, fn ($a, $b) => $b['match_score'] <=> $a['match_score']);
        return array_slice($out, 0, $limit);
    }

    private function bestCatalogRole(array $cand, ScoreService $svc): array
    {
        $bestKey = 'data_analyst'; $best = -1;
        foreach (Catalog::roles() as $key => $role) {
            $sc = $svc->match($cand, $role)['matchScore'];
            if ($sc > $best) { $best = $sc; $bestKey = $key; }
        }
        return [$bestKey, Catalog::role($bestKey)];
    }

    private function guessDomain(array $codes): string
    {
        if (array_intersect($codes, ['ui_ux', 'figma', 'graphic_design'])) return 'Design';
        if (array_intersect($codes, ['sql', 'python', 'data_analysis', 'dashboarding', 'statistics', 'machine_learning'])) return 'Data';
        if (array_intersect($codes, ['software', 'cloud', 'java', 'javascript', 'api'])) return 'Engineering';
        return 'Business';
    }

    private function normDomain(string $d): string
    {
        $d = ucfirst(strtolower(trim($d)));
        return in_array($d, ['Data', 'Engineering', 'Design', 'Business'], true) ? $d : 'Business';
    }
}
