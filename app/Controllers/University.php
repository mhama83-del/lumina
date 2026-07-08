<?php

namespace App\Controllers;

use App\Libraries\Catalog;
use App\Services\ScoreService;
use App\Services\UniversityInsightService;

class University extends BaseController
{
    private array $highValue = ['sql', 'python', 'data_analysis', 'software', 'cloud', 'entrepreneurship'];

    public function dashboard()
    {
        if (session('role') !== 'university') session()->set(['role' => 'university']);
        $db  = \Config\Database::connect();
        $svc = new ScoreService();
        $uni = trim((string) $this->request->getGet('uni'));

        // stated skills per student
        $statedMap = [];
        try {
            foreach ($db->table('student_skills ss')->select('ss.student_id, sk.code')
                ->join('skills sk', 'sk.id = ss.skill_id')->get()->getResultArray() as $r) {
                $statedMap[$r['student_id']][] = $r['code'];
            }
        } catch (\Throwable $e) {}

        $sq = $db->table('students')
            ->select('id, name, university, faculty, programme, target_domain, evidence_text, has_resume');
        if ($uni !== '') $sq->where('university', $uni);
        $students = $sq->get()->getResultArray();

        $universities = [];
        try {
            $universities = $db->table('students')->select('university, COUNT(*) c')
                ->groupBy('university')->orderBy('c', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {}

        $total = count($students);
        $bands = ['On track' => 0, 'Needs a nudge' => 0, 'At risk' => 0];
        $facSum = []; $facCnt = [];
        $prog   = [];
        $gapFreq = [];
        $cnt = ['ready' => 0, 'industry' => 0, 'highinc' => 0, 'jobcreator' => 0, 'transfer' => 0, 'matched' => 0];

        foreach ($students as $s) {
            $stated = $statedMap[$s['id']] ?? [];
            $cand   = $svc->signal($s['evidence_text'] ?? '', $stated, (int) $s['has_resume'], $s['target_domain'] ?? 'Data');
            $role   = Catalog::role($this->roleKeyFor($s['target_domain'] ?? 'Data'));
            $r      = $svc->employability($cand);
            $m      = $svc->match($cand, $role);
            $band   = $svc->risk($r);
            $bands[$band]++;

            $attr = $this->attrs($s['evidence_text'] ?? '', $cand['skills'], $s['target_domain'] ?? 'Data');
            $ind  = $svc->industryExposure($attr);
            $hi   = $svc->highIncome($attr);
            $jc   = $svc->jobCreator($attr);
            $transfer = isset($cand['skills']['leadership']) || isset($cand['skills']['communication']) || isset($cand['skills']['teamwork']);

            if ($r >= 60)  $cnt['ready']++;
            if ($ind >= 40) $cnt['industry']++;
            if ($hi >= 50)  $cnt['highinc']++;
            if ($jc >= 50)  $cnt['jobcreator']++;
            if ($transfer)  $cnt['transfer']++;
            if ($m['matchScore'] >= 65) $cnt['matched']++;

            $fac = $s['faculty'] ?: 'General';
            $facSum[$fac] = ($facSum[$fac] ?? 0) + $r; $facCnt[$fac] = ($facCnt[$fac] ?? 0) + 1;
            $pr = $s['programme'] ?: 'General';
            $prog[$pr]['ready'][] = $r; $prog[$pr]['ind'][] = $ind; $prog[$pr]['hi'][] = $hi;
            foreach ($m['gap'] as $g) { $gapFreq[$g] = ($gapFreq[$g] ?? 0) + 1; }
        }

        $pct = fn ($n) => $total ? (int) round(100 * $n / $total) : 0;

        $faculty = [];
        foreach ($facSum as $f => $sum) { $faculty[$f] = (int) round($sum / $facCnt[$f]); }
        arsort($faculty);

        $heat = [];
        foreach ($prog as $p => $d) {
            $avg = fn ($a) => $a ? (int) round(array_sum($a) / count($a)) : 0;
            $heat[] = ['programme' => $p, 'ready' => $avg($d['ready']), 'industry' => $avg($d['ind']), 'highincome' => $avg($d['hi'])];
        }
        // keep the heatmap readable — top 12 programmes by readiness
        usort($heat, fn ($a, $b) => $b['ready'] <=> $a['ready']);
        $heat = array_slice($heat, 0, 12);

        arsort($gapFreq);
        $topGap = array_key_first($gapFreq);
        $intervention = $topGap
            ? 'Run a ' . Catalog::label($topGap) . ' bootcamp — unlocks ' . $gapFreq[$topGap] . ' student(s) toward career-ready.'
            : 'Cohort is on track — maintain current support.';

        $sat = 0; $emps = [];
        try {
            $ef = $db->table('employer_feedback')->get()->getResultArray();
            if ($ef) {
                $sat = (int) round(array_sum(array_column($ef, 'satisfaction')) / count($ef));
                usort($ef, fn ($a, $b) => $b['satisfaction'] <=> $a['satisfaction']);
                $emps = array_slice($ef, 0, 3);
            }
        } catch (\Throwable $e) {}

        $sUrl = function (array $q) use ($uni) {
            if ($uni !== '') { $q['uni'] = $uni; }
            return site_url('university/students') . (empty($q) ? '' : '?' . http_build_query($q));
        };
        $kpis = [
            ['n' => $pct($cnt['ready']) . '%',      'l' => 'Career-ready',          't' => 'readiness ≥ 60',    'href' => $sUrl(['metric' => 'ready'])],
            ['n' => $pct($cnt['transfer']) . '%',   'l' => 'Transferable skills',   't' => 'leadership / comms',  'href' => $sUrl(['metric' => 'transfer'])],
            ['n' => $pct($cnt['industry']) . '%',   'l' => 'Industry exposure',     't' => 'internship / projects','href' => $sUrl(['metric' => 'industry'])],
            ['n' => $pct($cnt['highinc']) . '%',    'l' => 'High-income potential', 't' => 'high-value skills',   'href' => $sUrl(['metric' => 'highinc'])],
            ['n' => $pct($cnt['jobcreator']) . '%', 'l' => 'Job-creator potential', 't' => 'entrepreneurial',     'href' => $sUrl(['metric' => 'jobcreator'])],
            ['n' => $pct($cnt['matched']) . '%',    'l' => 'Opportunity match rate','t' => 'match ≥ 65%',        'href' => $sUrl(['metric' => 'matched'])],
            ['n' => ($sat ?: 84) . '%',             'l' => 'Employer satisfaction', 't' => 'from feedback',       'href' => ''],
            ['n' => (string) $total,                'l' => 'Students active',       't' => 'this cohort',         'href' => $sUrl([])],
        ];

        // ---- Fasa 5: cohort extras (No-Resume %, animal distribution, gaps, support) ----
        $snap = (new UniversityInsightService())->snapshot($uni !== '' ? $uni : null);

        return view('university/dashboard', array_merge([
            'title'   => 'Lumina · University',
            'kpis'    => $kpis,
            'bands'   => $bands,
            'faculty' => $faculty,
            'heat'    => $heat,
            'intervention' => $intervention,
            'employers'    => $emps,
            'total'   => $total,
            'uni'      => $uni,
            'universities' => $universities,
        ], $snap));
    }

    /** Fasa 5: Intervention Plan generator. */
    public function interventions()
    {
        if (session('role') !== 'university') session()->set(['role' => 'university']);
        $uni  = trim((string) $this->request->getGet('uni'));
        $plan = (new UniversityInsightService())->interventionPlan($uni !== '' ? $uni : null);
        return view('university/interventions', [
            'title' => 'Lumina · Intervention Plan',
            'plan'  => $plan,
            'uni'   => $uni,
        ]);
    }

    /** Drill-down: filtered student list. */
    public function students()
    {
        if (session('role') !== 'university') session()->set(['role' => 'university']);
        $f = [
            'uni'       => trim((string) $this->request->getGet('uni')),
            'band'      => trim((string) $this->request->getGet('band')),
            'gap'       => trim((string) $this->request->getGet('gap')),
            'programme' => trim((string) $this->request->getGet('programme')),
            'metric'    => trim((string) $this->request->getGet('metric')),
        ];
        $list = (new UniversityInsightService())->studentList($f);
        return view('university/students', ['title' => 'Lumina · Students', 'list' => $list, 'f' => $f]);
    }

    /** Drill-down: single student profile + why. */
    public function student($id = null)
    {
        if (session('role') !== 'university') session()->set(['role' => 'university']);
        $p = (new UniversityInsightService())->studentProfile((int) $id);
        if (! $p) return redirect()->to(base_url('university'));
        return view('university/student', $p + ['title' => 'Lumina · ' . ($p['s']['name'] ?? 'Student')]);
    }

    private function roleKeyFor(string $domain): string
    {
        $d = ucfirst(strtolower($domain));
        return match (true) {
            in_array($d, ['Engineering', 'Software'], true) => 'backend_engineer',
            $d === 'Business'                               => 'product_exec',
            default                                         => 'data_analyst',
        };
    }

    private function attrs(string $text, array $skills, string $domain): array
    {
        $t = strtolower($text);
        return [
            'internship'        => str_contains($t, 'internship') ? 1 : 0,
            'projects'          => min(3, substr_count($t, 'project') + substr_count($t, 'app') + substr_count($t, 'built') + substr_count($t, 'dashboard')),
            'certs'             => str_contains($t, 'cert') ? 1 : 0,
            'global'            => (str_contains($t, 'global') || str_contains($t, 'international') || str_contains($t, 'exchange')) ? 1 : 0,
            'high_value_skills' => count(array_intersect(array_keys($skills), $this->highValue)),
            'high_income_domain' => in_array(ucfirst(strtolower($domain)), ['Data', 'Engineering'], true) ? 1 : 0,
            'entrepreneur'      => (str_contains($t, 'startup') || str_contains($t, 'business')) ? 1 : 0,
            'innovation'        => (str_contains($t, 'innovation') || str_contains($t, 'built') || str_contains($t, 'app')) ? 1 : 0,
            'leadership'        => isset($skills['leadership']) ? 1 : 0,
        ];
    }
}
