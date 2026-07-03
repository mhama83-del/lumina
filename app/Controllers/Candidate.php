<?php

namespace App\Controllers;

use App\Libraries\WorkAnimal;
use App\Libraries\Explain;
use App\Services\ScoreService;

class Candidate extends BaseController
{
    private const SAMPLE_MYCSD =
        'Treasurer of the Robotics Club for 2 years; volunteered in a community coding programme; '
      . 'built an attendance app; led a small data analysis project for the faculty.';

    // ---------- Fasa 2 landing ----------
    public function home()
    {
        if (session('role') !== 'candidate') {
            session()->set(['role' => 'candidate', 'stage' => session('stage') ?? '19-22']);
        }
        return view('candidate/home', ['title' => 'Lumina · Candidate', 'persona' => session('persona') ?? []]);
    }

    // ---------- Fasa 3: entry chooser ----------
    public function start()
    {
        return view('candidate/start', ['title' => 'Lumina · Start']);
    }

    /** Use a ready sample persona, skip straight to a populated Passport. */
    public function sample()
    {
        $stage = session('stage') ?? '19-22';
        $p = $this->samplePreset($stage);
        $this->buildProfile($p['evidence_text'], $p['stated'], $p['domain'], $p['animal'], $p['verified'], $p['name']);
        return redirect()->to(base_url('passport'));
    }

    // ---------- Fasa 3: Work Animal ----------
    public function animal()
    {
        if ($this->request->getMethod() === 'POST') {
            $answers = $this->request->getPost('a') ?? [];
            $id = WorkAnimal::score(array_values($answers));
            $a = WorkAnimal::get($id);

            $profile = session('profile') ?? [];
            $profile['animal']      = $id;
            $profile['animalLabel'] = $a['label'];
            $profile['traits']      = $a['traits'];
            $profile['domains']     = $a['domains'];
            session()->set('profile', $profile);

            return redirect()->to(base_url('onboard/input'));
        }
        return view('candidate/animal', [
            'title'     => 'Lumina · Work Animal',
            'questions' => WorkAnimal::questions(),
        ]);
    }

    // ---------- Fasa 3: evidence input ----------
    public function input()
    {
        if ($this->request->getMethod() === 'POST') {
            $method  = $this->request->getPost('method');
            $profile = session('profile') ?? [];
            $domain  = $profile['domains'][0] ?? 'Data';

            if ($method === 'transcript') {
                $text = self::SAMPLE_MYCSD; $verified = 1; $stated = [];
            } elseif ($method === 'fiveq') {
                $interest = $this->request->getPost('q_interest') ?: 'Data';
                $subject  = $this->request->getPost('q_subject') ?: '';
                $activity = $this->request->getPost('q_activity') ?: '';
                $text = trim("Interested in {$interest}. Strong in {$subject}. {$activity}");
                $stated = $this->subjectToSkills($subject);
                $domain = $this->normaliseDomain($interest);
                $verified = 0;
            } else { // paste
                $text = trim((string) $this->request->getPost('evidence_text'));
                $stated = []; $verified = 0;
            }

            $this->buildProfile($text, $stated, $domain, $profile['animal'] ?? null, $verified, $profile['name'] ?? null);
            return redirect()->to(base_url('passport'));
        }
        return view('candidate/input', [
            'title'  => 'Lumina · Build your portfolio',
            'sample' => self::SAMPLE_MYCSD,
        ]);
    }

    // ---------- Fasa 3: Living Portfolio ----------
    public function passport()
    {
        $profile = session('profile');
        if (! $profile) { $this->sample(); $profile = session('profile'); }

        $svc  = new ScoreService();
        $cand = $this->buildSignal($profile);
        $role = $this->targetRoleFor($profile['target_domain'] ?? 'Data');

        $readiness = $svc->readiness($cand, $role);
        $match     = $svc->match($cand, $role);
        $risk      = $svc->risk($readiness['score']);

        return view('candidate/passport', [
            'title'     => 'Lumina · Living Portfolio',
            'profile'   => $profile,
            'readiness' => $readiness,
            'match'     => $match,
            'risk'      => $risk,
            'role'      => $role,
            'whyText'   => Explain::readiness($readiness),
        ]);
    }

    // ---------- Fasa 4: Career Compass ----------
    public function compass()
    {
        $profile = session('profile');
        if (! $profile) { $this->sample(); $profile = session('profile'); }

        $svc  = new ScoreService();
        $cand = $this->buildSignal($profile);

        $paths = [];
        foreach ($this->pathRoles() as $role) {
            $r = $svc->readiness($cand, $role);
            $m = $svc->match($cand, $role);
            $paths[] = [
                'key'       => $role['key'],
                'title'     => $role['title'],
                'color'     => $role['color'],
                'colorHex'  => $role['hex'],
                'readiness' => $r['score'],
                'label'     => $m['label'],
                'match'     => $m['matchScore'],
                'gaps'      => array_map(fn ($c) => ['code' => $c, 'label' => $this->skillLabel($c)], $m['gap']),
                'plan'      => $this->planFor($m['gap']),
                'traj'      => $this->trajectoryFor($svc, $cand, $role, $m['gap']),
            ];
        }
        usort($paths, fn ($a, $b) => $b['readiness'] <=> $a['readiness']);

        return view('candidate/compass', [
            'title'   => 'Lumina · Career Compass',
            'profile' => $profile,
            'paths'   => $paths,
        ]);
    }

    /** AJAX: recompute readiness for a path after adding skills (What-If). */
    public function whatif()
    {
        $profile = session('profile') ?? [];
        $cand    = $this->buildSignal($profile);
        $key     = (string) $this->request->getPost('role');
        $add     = $this->request->getPost('add') ?? [];
        $roles   = $this->pathRoles();
        $role    = $roles[$key] ?? array_values($roles)[0];

        $svc = new ScoreService();
        $w   = $svc->whatIf($cand, $role, is_array($add) ? $add : []);
        return $this->response->setJSON($w);
    }

    // ---------- Stubs (Fasa 5+) ----------
    public function smatch()  { return $this->soon('Smart Matching', 'Fasa 5', 'Best / Growth / Stretch opportunities with reasons.'); }
    public function placed()  { return $this->soon('Placed / Growing', 'Fasa 6', 'Your career keeps growing after you are hired.'); }
    private function soon($name, $phase, $desc) { return view('home/soon', compact('name', 'phase', 'desc') + ['title' => "Lumina · $name"]); }

    // ================= helpers =================

    private function buildProfile(string $text, array $stated, string $domain, ?string $animal, int $verified, ?string $name): void
    {
        $svc = new ScoreService();
        $skills = $svc->inferSkills($text, $stated);

        $profile = session('profile') ?? [];
        $profile = array_merge($profile, [
            'name'          => $name ?: ($profile['name'] ?? 'You'),
            'stage'         => session('stage') ?? '19-22',
            'animal'        => $animal ?? ($profile['animal'] ?? null),
            'evidence_text' => $text,
            'skills'        => $skills,
            'verified'      => $verified,
            'target_domain' => $this->normaliseDomain($domain),
        ]);
        session()->set('profile', $profile);
    }

    private function buildSignal(array $profile): array
    {
        $t = strtolower($profile['evidence_text'] ?? '');
        $projects   = substr_count($t, 'project') + substr_count($t, 'app') + substr_count($t, 'built');
        $activities = (int) (str_contains($t, 'club') || str_contains($t, 'treasurer'))
                    + (int) str_contains($t, 'volunteer')
                    + (int) (str_contains($t, 'led') || str_contains($t, 'president'));
        return [
            'skills'     => $profile['skills'] ?? [],
            'top_domain' => $profile['target_domain'] ?? 'Data',
            'verified'   => $profile['verified'] ?? 0,
            'projects'   => max(1, $projects),
            'activities' => max(1, $activities),
            'pace'       => 'Steady',
        ];
    }

    private function targetRoleFor(string $domain): array
    {
        return match ($this->normaliseDomain($domain)) {
            'Engineering' => ['title' => 'Backend Engineer', 'domain' => 'Engineering', 'required' => ['software', 'cloud', 'python', 'communication'], 'color' => 'var(--teal)'],
            'Business'    => ['title' => 'Product Executive', 'domain' => 'Business',    'required' => ['stakeholder_mgmt', 'communication', 'leadership'], 'color' => 'var(--gold)'],
            default       => ['title' => 'Data Analyst',      'domain' => 'Data',        'required' => ['sql', 'dashboarding', 'python', 'data_analysis'], 'color' => 'var(--indigo)'],
        };
    }

    private function normaliseDomain(string $d): string
    {
        $d = ucfirst(strtolower(trim($d)));
        if (in_array($d, ['Data', 'Research'], true))                return 'Data';
        if (in_array($d, ['Engineering', 'Software', 'Tech'], true)) return 'Engineering';
        return 'Business';
    }

    private function subjectToSkills(string $subject): array
    {
        $s = strtolower($subject); $out = [];
        if (str_contains($s, 'math') || str_contains($s, 'data'))      $out[] = 'data_analysis';
        if (str_contains($s, 'comput') || str_contains($s, 'program')) $out[] = 'software';
        if (str_contains($s, 'business') || str_contains($s, 'commun')) $out[] = 'communication';
        return $out;
    }

    // ---------- Fasa 4 helpers ----------
    private function pathRoles(): array
    {
        return [
            'data_analyst'     => ['key' => 'data_analyst',     'title' => 'Data Analyst',      'domain' => 'Data',        'color' => 'var(--indigo)', 'hex' => '#6D5DFB', 'required' => ['sql', 'dashboarding', 'python', 'data_analysis']],
            'backend_engineer' => ['key' => 'backend_engineer', 'title' => 'Backend Engineer',  'domain' => 'Engineering', 'color' => 'var(--teal)',   'hex' => '#14B8A6', 'required' => ['software', 'cloud', 'python', 'communication']],
            'product_exec'     => ['key' => 'product_exec',     'title' => 'Product Executive', 'domain' => 'Business',    'color' => 'var(--gold)',   'hex' => '#F5C518', 'required' => ['stakeholder_mgmt', 'communication', 'leadership']],
        ];
    }

    private function skillLabel(string $c): string
    {
        $m = ['sql' => 'SQL', 'dashboarding' => 'Dashboarding', 'python' => 'Python', 'data_analysis' => 'Data Analysis',
              'software' => 'Software Dev', 'cloud' => 'Cloud', 'communication' => 'Communication',
              'stakeholder_mgmt' => 'Stakeholder Mgmt', 'leadership' => 'Leadership', 'budgeting' => 'Budgeting', 'teamwork' => 'Teamwork'];
        return $m[$c] ?? ucwords(str_replace('_', ' ', $c));
    }

    private function planFor(array $gap): array
    {
        $g = array_map(fn ($c) => $this->skillLabel($c), $gap);
        return [
            ['d' => '30 days', 't' => $g ? 'Learn ' . $g[0] : 'Strengthen your fundamentals'],
            ['d' => '60 days', 't' => isset($g[1]) ? 'Build a project using ' . $g[0] . ' + ' . $g[1] : ($g ? 'Build a project using ' . $g[0] : 'Build a portfolio project')],
            ['d' => '90 days', 't' => $g ? 'Add evidence / a cert in ' . implode(', ', array_slice($g, 0, 2)) : 'Get an internship or verified evidence'],
        ];
    }

    private function trajectoryFor(ScoreService $svc, array $cand, array $role, array $gap): array
    {
        return [
            'now' => $svc->readiness($cand, $role)['score'],
            'd30' => $svc->whatIf($cand, $role, array_slice($gap, 0, 1))['after'],
            'd60' => $svc->whatIf($cand, $role, array_slice($gap, 0, 2))['after'],
            'd90' => $svc->whatIf($cand, $role, $gap)['after'],
        ];
    }

    private function samplePreset(string $stage): array
    {
        $presets = [
            '16-18'  => ['name' => 'Nurul',   'evidence_text' => 'Active in Science Club; built a small weather logger; enjoys maths.', 'stated' => ['data_analysis'], 'domain' => 'Data', 'animal' => 'owl', 'verified' => 0],
            '19-22'  => ['name' => 'Aiman',   'evidence_text' => self::SAMPLE_MYCSD, 'stated' => ['python', 'teamwork'], 'domain' => 'Data', 'animal' => 'owl', 'verified' => 1],
            '23-28'  => ['name' => 'Wei Jie', 'evidence_text' => 'Internship at a fintech; built dashboards; led a small analytics team.', 'stated' => ['sql', 'dashboarding', 'python'], 'domain' => 'Data', 'animal' => 'fox', 'verified' => 1],
            '26-28+' => ['name' => 'Sara',    'evidence_text' => '3 years backend dev; mentors juniors; shipped cloud microservices.', 'stated' => ['software', 'cloud', 'communication'], 'domain' => 'Engineering', 'animal' => 'eagle', 'verified' => 1],
        ];
        return $presets[$stage] ?? $presets['19-22'];
    }
}
