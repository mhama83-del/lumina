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

    /** The 11 Lumina domains (ISCED-F 2013 grounded). */
    private const VALID_DOMAINS = [
        'Data', 'Engineering', 'Design', 'Business',
        'Education', 'Arts & Humanities', 'Social Sciences', 'Natural Sciences',
        'Agriculture & Veterinary', 'Health & Welfare', 'Services',
    ];

    // ---------- Fasa 2 landing ----------
    public function home()
    {
        if (session('role') !== 'candidate') {
            session()->set(['role' => 'candidate', 'stage' => session('stage') ?? '19-22']);
        }
        return view('candidate/home', ['title' => 'Lumina · Candidate', 'profile' => session('profile') ?? null]);
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
            $profile['quiz_ps']     = WorkAnimal::psScore(array_values($answers));
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
            if ($method === 'guided') { return $this->buildGuided(); }
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
        $role = $this->targetRoleFor($profile['target_domain'] ?? 'Data', $cand);

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
            'potentialProfile' => $profile['potential_profile'] ?? null,
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
        foreach ($this->pathRoles($cand) as $role) {
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
            // Strategic B5: full catalog for "Explore Another Career".
            'allRoles' => \App\Libraries\Catalog::roles(),
        ]);
    }

    /** AJAX: recompute readiness for a path after adding skills (What-If). */
    public function whatif()
    {
        $profile = session('profile') ?? [];
        $cand    = $this->buildSignal($profile);
        $key     = (string) $this->request->getPost('role');
        $add     = $this->request->getPost('add') ?? [];
        $roles   = $this->pathRoles($cand);
        $role    = $roles[$key] ?? array_values($roles)[0];

        $svc = new ScoreService();
        $w   = $svc->whatIf($cand, $role, is_array($add) ? $add : []);
        return $this->response->setJSON($w);
    }

    /**
     * Career Explorer (Fasa B5 — Strategic B): compute fit against ANY role
     * in the full catalog, not just the 3 preset paths. Reuses buildSignal(),
     * planFor(), trajectoryFor(), skillLabel() and ScoreService::readiness()/
     * match() as-is — no new scoring logic. Additive: new route + method.
     */
    public function exploreCareer()
    {
        $key   = (string) $this->request->getPost('role');
        $roles = \App\Libraries\Catalog::roles();
        $role  = $roles[$key] ?? null;
        if (! $role) {
            return $this->response->setJSON(['error' => 'invalid_role']);
        }

        $profile = session('profile');
        if (! $profile) { $this->sample(); $profile = session('profile'); }

        $svc  = new ScoreService();
        $cand = $this->buildSignal($profile);

        $readiness = $svc->readiness($cand, $role);
        $match     = $svc->match($cand, $role);
        $gap       = $match['gap'];

        // Explorer-specific, positive-only labels — cosmetic remap only,
        // reuses the Protected Baseline match() label/thresholds as-is.
        $fitLabel = [
            'best'    => 'Ready Now',
            'growth'  => 'Reachable',
            'stretch' => 'Longer Path',
        ][$match['label']] ?? 'Reachable';

        // Remember the chosen career for later phases (e.g. B6 Growth Pathway).
        $profile['chosen_career'] = $key;
        session()->set('profile', $profile);

        return $this->response->setJSON([
            'key'       => $role['key'],
            'title'     => $role['title'],
            'color'     => $role['color'],
            'colorHex'  => $role['hex'],
            'readiness' => $readiness['score'],
            'fitLabel'  => $fitLabel,
            'matched'   => array_map(fn ($c) => ['code' => $c, 'label' => $this->skillLabel($c)], $match['matched']),
            'gaps'      => array_map(fn ($c) => ['code' => $c, 'label' => $this->skillLabel($c)], $gap),
            'plan'      => $this->planFor($gap),
            'traj'      => $this->trajectoryFor($svc, $cand, $role, $gap),
        ]);
    }

    // ---------- Fasa 5: Smart Matching (candidate) ----------
    public function smatch()
    {
        $profile = session('profile');
        if (! $profile) { $this->sample(); $profile = session('profile'); }

        $svc  = new ScoreService();
        $cand = $this->buildSignal($profile);

        $opps = [];
        foreach (\App\Libraries\Catalog::roles() as $role) {
            $m = $svc->match($cand, $role);
            $opps[] = [
                'title'   => $role['title'],
                'company' => $role['company'],
                'location' => $role['location'],
                'salary'  => $role['salary'],
                'color'   => $role['color'],
                'match'   => $m['matchScore'],
                'label'   => $m['label'],
                'matched' => \App\Libraries\Catalog::labels($m['matched']),
                'gap'     => \App\Libraries\Catalog::labels($m['gap']),
                'reason'  => \App\Libraries\Explain::match($m, $role['domain']),
            ];
        }
        usort($opps, fn ($a, $b) => $b['match'] <=> $a['match']);
        $opps = array_slice($opps, 0, 3);
        $display = ['Best fit', 'Growth fit', 'Stretch fit'];
        foreach ($opps as $i => &$o) { $o['fit'] = $display[$i] ?? 'Fit'; }
        unset($o);

        return view('candidate/match', [
            'title'   => 'Lumina · Smart Matching',
            'profile' => $profile,
            'opps'    => $opps,
        ]);
    }

    public function placed()  { return $this->soon('Placed / Growing', 'Fasa 6', 'Your career keeps growing after you are hired.'); }

    private function soon($name, $phase, $desc) { return view('home/soon', compact('name', 'phase', 'desc') + ['title' => "Lumina · $name"]); }

    // ---------- Resume Analysis (v1 feature, simulated AI) ----------
    public function resume()
    {
        return view('candidate/resume', ['title' => 'Lumina · Resume Analysis']);
    }

    /**
     * AJAX (lightweight, no persist): read the pasted resume just enough to
     * detect a name + a few highlight clauses, so the candidate can confirm
     * or correct their profile BEFORE the full AI analysis runs.
     */
    public function resumePreview()
    {
        $text = trim((string) $this->request->getPost('resume_text'));
        if ($text === '') return $this->response->setJSON(['error' => 'empty']);

        $name   = $this->extractName($text);
        $parser = new \App\Services\ResumeParserService();
        $highlights = array_slice(array_values(array_unique(array_merge(
            $parser->detectLeadership($text),
            $parser->detectProjects($text)
        ))), 0, 4);

        return $this->response->setJSON(['name' => $name, 'highlights' => $highlights]);
    }

    /**
     * Deterministic name detector (no external AI): looks at the first few
     * lines for something that reads like "Firstname Lastname", else a
     * "Name: ..." label. Returns '' if nothing confident is found — the
     * candidate can just type it in on the confirm step.
     */
    private function extractName(string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        $stop = ['resume', 'curriculum vitae', 'cv', 'objective', 'summary', 'profile', 'contact',
                 'email', 'phone', 'address', 'experience', 'education', 'skills', 'final-year', 'university'];

        foreach (array_slice($lines, 0, 3) as $line) {
            $line = trim($line, " \t\-–•·");
            if ($line === '' || mb_strlen($line) > 40) continue;
            $lc = strtolower($line);
            $skip = false;
            foreach ($stop as $sw) { if (str_contains($lc, $sw)) { $skip = true; break; } }
            if ($skip) continue;
            if (preg_match('/^([A-Z][a-zA-Z\'\-]+(?:\s+[A-Z][a-zA-Z\'\-]+){1,3})$/', $line, $m)) {
                return $m[1];
            }
        }
        if (preg_match('/name\s*[:\-]\s*([A-Za-z\'\-]+(?:\s+[A-Za-z\'\-]+){1,3})/i', $text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    /** AJAX: analyse pasted resume text, build profile, persist, return JSON. */
    public function resumeAnalyze()
    {
        $text = trim((string) $this->request->getPost('resume_text'));
        if ($text === '') return $this->response->setJSON(['error' => 'empty']);
        $confirmedName = trim((string) $this->request->getPost('name'));

        $svc       = new ScoreService();
        $parser    = new \App\Services\ResumeParserService();
        $explained = $svc->inferSkillsExplained($text, []);

        // ---- Field of study: read what the candidate actually STUDIED first.
        // This is a stronger, more trustworthy signal than generic soft-skill
        // keywords (communication, teamwork, ...) that appear in almost every
        // resume regardless of field. Falls back to skill-keyword majority
        // vote only when no degree/programme line is detected.
        $degree = $parser->extractFieldOfStudy($text);
        $domain = $this->guessDomain(array_keys($explained), $degree['domain'] ?? null);

        // persist profile (session) so the user can continue into Compass/Match
        $this->buildProfile(
            $text, [], $domain, session('profile')['animal'] ?? null, 0,
            $confirmedName ?: (session('profile')['name'] ?? 'You')
        );
        // Ground initial matching in skills GAINED THROUGH THE PROGRAMME
        // (e.g. Chemical Engineering -> chemical_processing, process_safety),
        // not just whatever soft-skill keywords happen to appear in the
        // activities/leadership text. Additive only — never overrides an
        // already explicitly-detected skill.
        $this->injectCoreSkills($degree['coreSkills'] ?? []);
        $cand = $this->buildSignal(session('profile'));

        // top role matches
        $matches = [];
        foreach (\App\Libraries\Catalog::roles() as $key => $role) {
            $m = $svc->match($cand, $role);
            $matches[] = ['key' => $key, 'title' => $role['title'], 'company' => $role['company'],
                          'domain' => $role['domain'], 'color' => $role['hex'],
                          'match' => $m['matchScore'], 'label' => $m['label'],
                          'gap' => \App\Libraries\Catalog::labels($m['gap'])];
        }
        usort($matches, fn ($a, $b) => $b['match'] <=> $a['match']);

        // If we detected a clear field of study, pin the best-fitting role in
        // that field to the #1 spot — a Chemical Engineering degree should
        // outrank a generic Business role even when the Business role scores
        // marginally higher purely on generic soft-skill overlap.
        if (! empty($degree['domain'])) {
            $sameField = array_values(array_filter($matches, fn ($x) => $x['domain'] === $degree['domain']));
            if ($sameField) {
                $pinned = $sameField[0];
                $rest   = array_values(array_filter($matches, fn ($x) => $x['key'] !== $pinned['key']));
                $matches = array_merge([$pinned], $rest);
            }
        }
        $top = array_slice($matches, 0, 3);

        // readiness vs the best-fit role — prefer the exact role hinted by the
        // detected degree, else the best role within the detected domain,
        // else the global best match.
        $bestRoleKey = (! empty($degree['role']) && isset(\App\Libraries\Catalog::roles()[$degree['role']]))
            ? $degree['role']
            : $this->bestRoleKey($cand, $svc, $degree['domain'] ?? null);
        $bestRole  = \App\Libraries\Catalog::role($bestRoleKey);
        $bestM     = $svc->match($cand, $bestRole);
        $readiness = $svc->readiness($cand, $bestRole);
        $band      = $svc->risk($readiness['score']);

        $skillOut = [];
        foreach ($cand['skills'] as $code => $s) {
            $skillOut[] = [
                'label'  => \App\Libraries\Catalog::label($code),
                'source' => $s['source'] ?? 'inferred',
                'conf'   => round(($s['confidence'] ?? 0.6) * 100),
                'from'   => $s['from'] ?? (($s['source'] ?? '') === 'programme' ? ('from your field of study: ' . ($degree['raw'] ?? '')) : null),
                'evidence_label' => $s['evidence_label'] ?? (($s['source'] ?? '') === 'stated' ? 'Stated' : 'Inferred'),
            ];
        }

        // detect a university named in the resume (optional display)
        $foundUni = null;
        foreach (['UM','USM','UKM','UPM','UTM','UiTM','UUM','IIUM','UMS','UNIMAS','UMT','UPSI','USIM','UMK','UTHM','UTeM','UMP','UniMAP','Taylor','Sunway','MMU','UTP','UNITEN','UCSI','APU','Monash','Nottingham','Xiamen','Heriot'] as $u) {
            if (stripos($text, $u) !== false) { $foundUni = $u; break; }
        }

        // benchmark this readiness against the cohort (same field)
        $cohort = $this->benchmark($svc, $domain, $readiness['score']);

        // ---- Fasa 2: richer extracted profile + recommendations ----
        $rec    = new \App\Services\RecommendationService();
        $projects    = $parser->detectProjects($text);
        $leadership  = $parser->detectLeadership($text);
        $cluster     = $parser->careerCluster($domain);
        $animal      = $parser->animalFromEvidence($cand['skills'], $text);
        $internships = $rec->internships($domain);
        $feedback    = $rec->feedback($text, $cand['skills'], $readiness['score'], $projects, $leadership);
        $nextAction  = $rec->nextAction($band, $bestM['gap'], $bestM['matched']);
        $courses     = $rec->microCourses($bestM['gap']);
        $resumeCoach = $rec->resumeCoach(
            $text, $cand['skills'], $projects, $leadership,
            $bestM['gap'], $bestRole['title'] ?? null, $readiness['score']
        );

        // ---- Lumina Graph: enrich + learn ----
        $tax        = new \App\Services\TaxonomyService();
        $detCodes   = array_keys($cand['skills']);
        $graph      = $tax->enrich($detCodes, '', $domain);
        $graphLearn = $tax->learn($detCodes, $text, '', $domain);
        $graphStats = $tax->stats();
        $novel      = $tax->extractNovel($text);
        $graphNew   = $tax->learnNovel($novel, $domain, $detCodes, '');
        $tax->reinforce(array_merge($detCodes, array_column($novel, 'code')));
        $graphStats = $tax->stats();

        // ---- Fasa 2: persist to DB (demo-safe: never breaks the demo) ----
        $savedId = null;
        try {
            $sk   = session_id();
            $name = session('profile')['name'] ?? 'You';
            $savedId = (new \App\Models\ResumeAnalysisModel())->insert([
                'session_key'        => $sk,
                'source'             => 'resume',
                'name'               => $name,
                'raw_text'           => $text,
                'target_domain'      => $domain,
                'career_cluster'     => $cluster,
                'readiness'          => $readiness['score'],
                'employability_band' => $band,
                'animal_primary'     => $animal['primary']['id'],
                'animal_secondary'   => $animal['secondary']['id'],
                'animal_growth'      => $animal['growth']['id'],
                'skills_json'        => json_encode($skillOut),
                'projects_json'      => json_encode($projects),
                'leadership_json'    => json_encode($leadership),
                'feedback_json'      => json_encode($feedback),
                'next_action'        => $nextAction,
            ], true);
            (new \App\Models\CandidateProfileModel())->insert([
                'analysis_id'   => $savedId,
                'session_key'   => $sk,
                'name'          => $name,
                'stage'         => session('stage') ?? '19-22',
                'target_domain' => $domain,
                'animal'        => $animal['primary']['id'],
                'verified'      => 0,
                'evidence_text' => $text,
                'skills_json'   => json_encode(array_keys($cand['skills'])),
                'readiness'     => $readiness['score'],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Lumina persist failed: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'skills'     => $skillOut,
            'readiness'  => $readiness['score'],
            'matches'    => $top,
            'domain'     => $domain,
            'field'      => $this->fieldName($domain) . (! empty($degree['raw']) ? ' · Field of study detected: ' . $degree['raw'] : ''),
            'university' => $foundUni,
            'cohort'     => $cohort,
            'name'       => session('profile')['name'] ?? 'You',
            // ---- Fasa 2 additions ----
            'career_cluster' => $cluster,
            'band'           => $band,
            'projects'       => $projects,
            'leadership'     => $leadership,
            'animal'         => $animal,
            'internships'    => $internships,
            'feedback'       => $feedback,
            'next_action'    => $nextAction,
            'courses'        => $courses,
            'resume_coach'   => $resumeCoach,
            'saved'          => (bool) $savedId,
            'saved_id'       => $savedId,
            'graph_related'  => $graph['related'],
            'graph_added'    => count($graphLearn['added_skills']),
            'graph_stats'    => $graphStats,
            'graph_new_skills' => $graphNew,
        ]);
    }

    /** Where does this readiness sit among cohort students of the same domain? */
    private function benchmark(ScoreService $svc, string $domain, int $myReadiness): array
    {
        try {
            $peers = \Config\Database::connect()->table('students')
                ->select('evidence_text, has_resume')->where('target_domain', $domain)
                ->get()->getResultArray();
        } catch (\Throwable $e) { $peers = []; }

        $n = count($peers); $below = 0; $sum = 0;
        foreach ($peers as $p) {
            $pc = $svc->signal($p['evidence_text'] ?? '', [], (int) ($p['has_resume'] ?? 0), $domain);
            $pr = $svc->readiness($pc, \App\Libraries\Catalog::role($this->bestRoleKey($pc, $svc, $domain)))['score'];
            $sum += $pr;
            if ($pr < $myReadiness) $below++;
        }
        return [
            'domain'     => $domain,
            'size'       => $n,
            'percentile' => $n ? (int) round(100 * $below / $n) : 0,
            'avg'        => $n ? (int) round($sum / $n) : 0,
            'you'        => $myReadiness,
        ];
    }

    /**
     * Best matching role key. If $domainHint is given, restricts the search
     * to roles in that domain first (falls back to a global search only if
     * that domain has no roles at all) — so a detected field of study keeps
     * the readiness/gap calculation anchored to the right domain.
     */
    private function bestRoleKey(array $cand, ScoreService $svc, ?string $domainHint = null): string
    {
        $roles = \App\Libraries\Catalog::roles();

        if ($domainHint) {
            $inDomain = array_filter($roles, fn ($r) => $r['domain'] === $domainHint);
            if ($inDomain) {
                $bestKey = array_key_first($inDomain); $bestScore = -1;
                foreach ($inDomain as $key => $role) {
                    $m = $svc->match($cand, $role);
                    if ($m['matchScore'] > $bestScore) { $bestScore = $m['matchScore']; $bestKey = $key; }
                }
                return $bestKey;
            }
        }

        $bestKey = 'data_analyst'; $bestScore = -1;
        foreach ($roles as $key => $role) {
            $m = $svc->match($cand, $role);
            if ($m['matchScore'] > $bestScore) { $bestScore = $m['matchScore']; $bestKey = $key; }
        }
        return $bestKey;
    }

    private function fieldName(string $domain): string
    {
        return [
            'Data'                     => 'Data, Analytics & Computing',
            'Engineering'              => 'Engineering & Technology',
            'Design'                   => 'Design & Creative',
            'Business'                 => 'Business, Finance & Communication',
            'Education'                => 'Education & Training',
            'Arts & Humanities'        => 'Arts, Culture & Humanities',
            'Social Sciences'         => 'Social Sciences & Public Affairs',
            'Natural Sciences'        => 'Natural Sciences & Research',
            'Agriculture & Veterinary' => 'Agriculture & Veterinary Sciences',
            'Health & Welfare'        => 'Health & Welfare',
            'Services'                 => 'Hospitality, Tourism & Services',
        ][$domain] ?? $domain;
    }

    /**
     * $override (from a detected field of study) always wins when present —
     * what the candidate studied is a stronger signal than skill-keyword
     * counting. Falls back to majority-vote across skill buckets otherwise
     * (not first-match-wins: a single stray "analysis" mention should not
     * override a resume full of engineering signals). Covers all 11 domains.
     */
    private function guessDomain(array $codes, ?string $override = null): string
    {
        if ($override) return $override;

        $buckets = [
            'Design'      => ['ui_ux', 'figma', 'graphic_design'],
            'Data'        => ['sql', 'python', 'data_analysis', 'dashboarding', 'statistics', 'machine_learning'],
            'Engineering' => ['software', 'cloud', 'java', 'javascript', 'api',
                'mechanical_design', 'cad', 'fea', 'manufacturing', 'six_sigma',
                'aerodynamics', 'aircraft_systems', 'avionics', 'structural_engineering',
                'circuit_design', 'plc', 'electrical_schematics', 'embedded', 'robotics', 'control_systems',
                'process_safety', 'chemical_processing', 'project_planning'],
            'Education'   => ['lesson_planning', 'classroom_mgmt', 'curriculum_design', 'training_design'],
            'Arts & Humanities' => ['translation', 'performance', 'stage_presence', 'archival_work', 'creativity'],
            'Social Sciences'  => ['interviewing', 'policy_analysis', 'case_mgmt', 'counselling'],
            'Natural Sciences' => ['lab_techniques', 'quality_control', 'risk_modeling', 'scientific_writing'],
            'Agriculture & Veterinary' => ['crop_science', 'field_research', 'sustainability', 'animal_care', 'aquaculture'],
            'Health & Welfare' => ['clinical_skills', 'patient_care', 'pharmacology', 'rehabilitation', 'public_health'],
            'Services'    => ['hospitality_ops', 'event_planning', 'logistics'],
        ];
        $best = 'Business'; $bestCount = 0;
        foreach ($buckets as $dom => $set) {
            $count = count(array_intersect($codes, $set));
            if ($count > $bestCount) { $bestCount = $count; $best = $dom; }
        }
        return $best;
    }

    private function roleKeyForDomain(string $d): string
    {
        return match ($d) { 'Engineering' => 'backend_engineer', 'Business' => 'product_exec', default => 'data_analyst' };
    }

    // ================= helpers =================

    /**
     * Merge skills typically GAINED through the candidate's declared
     * programme into their session profile (tagged source='programme').
     * These ground the initial job-description matching; they are additive
     * and never override an already-detected, explicitly-written skill.
     */
    private function injectCoreSkills(array $codes): void
    {
        if (! $codes) return;
        $profile = session('profile') ?? [];
        foreach ($codes as $code) {
            if (! isset($profile['skills'][$code])) {
                $profile['skills'][$code] = ['confidence' => 0.6, 'source' => 'programme'];
            }
        }
        session()->set('profile', $profile);
    }

    private function buildProfile(string $text, array $stated, string $domain, ?string $animal, int $verified, ?string $name): void
    {
        $svc = new ScoreService();
        // Strategic B3: explained variant adds 'from' (trigger word); each
        // skill is then labelled with its canonical evidence status.
        // Additive — readiness()/match() only read 'confidence'/'source'.
        $skills = $svc->inferSkillsExplained($text, $stated);
        $skills = (new \App\Services\EvidenceCheckService())->label($skills, $verified);
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

        $profile['potential_profile'] = (new \App\Services\PotentialProfileService())->build(
            $profile['quiz_ps'] ?? [],
            $text,
            $skills
        );

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

    /** Best-fitting role for a domain, chosen dynamically from the shared Catalog (not hardcoded). */
    private function targetRoleFor(string $domain, ?array $cand = null): array
    {
        $domain = $this->normaliseDomain($domain);
        $inDomain = array_values(array_filter(\App\Libraries\Catalog::roles(), fn ($r) => $r['domain'] === $domain));
        $best = $inDomain[0] ?? null;
        if ($cand && count($inDomain) > 1) {
            $svc = new ScoreService(); $bestScore = -1;
            foreach ($inDomain as $r) {
                $m = $svc->match($cand, $r)['matchScore'];
                if ($m > $bestScore) { $bestScore = $m; $best = $r; }
            }
        }
        if (! $best) {
            return ['title' => 'Data Analyst', 'domain' => 'Data', 'required' => ['sql', 'dashboarding', 'python', 'data_analysis'], 'color' => 'var(--indigo)'];
        }
        return ['title' => $best['title'], 'domain' => $best['domain'], 'required' => $best['required'], 'color' => $best['color']];
    }

    /**
     * Pass any of the 11 valid Lumina domains through unchanged (case-insensitive).
     * Keeps a few legacy aliases for backward compatibility with older callers
     * (fiveq/guided flows that still say "Research", "Software", "Creative").
     * Anything unrecognised falls back to Business (broadest catch-all domain).
     */
    private function normaliseDomain(string $d): string
    {
        $d = trim($d);
        foreach (self::VALID_DOMAINS as $v) {
            if (strcasecmp($d, $v) === 0) return $v;
        }
        $d2 = ucfirst(strtolower($d));
        if (in_array($d2, ['Research'], true)) return 'Data';
        if (in_array($d2, ['Software', 'Tech'], true)) return 'Engineering';
        if (in_array($d2, ['Creative'], true)) return 'Design';
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

    // ---------- Fasa 3: guided No-Resume builder ----------
    private function buildGuided()
    {
        $r = $this->request;
        $programme  = trim((string) $r->getPost('g_programme'));
        $stage      = trim((string) $r->getPost('g_stage')) ?: (session('stage') ?? '19-22');
        $cgpaRaw    = trim((string) $r->getPost('g_cgpa'));
        $activities = trim((string) $r->getPost('g_activities'));
        $leadership = trim((string) $r->getPost('g_leadership'));
        $projects   = trim((string) $r->getPost('g_projects'));
        $tools      = trim((string) $r->getPost('g_tools'));
        $comps      = trim((string) $r->getPost('g_competitions'));
        $intern     = trim((string) $r->getPost('g_internship')) ?: 'none';
        $interest   = trim((string) $r->getPost('g_interest')) ?: 'Data';
        $prefRole   = trim((string) $r->getPost('g_role'));
        $ws1        = trim((string) $r->getPost('g_ws1'));
        $ws2        = trim((string) $r->getPost('g_ws2'));

        // assemble evidence text from the guided answers
        $parts = [];
        if ($programme)  $parts[] = "Studying {$programme}.";
        if ($leadership) $parts[] = $leadership . ($activities ? " of {$activities}." : ".");
        elseif ($activities) $parts[] = "Active in {$activities}.";
        if ($projects)   $parts[] = $projects . ".";
        if ($tools)      $parts[] = "Tools: {$tools}.";
        if ($comps)      $parts[] = "Competed in {$comps}.";
        if ($intern === 'completed') $parts[] = "Completed an internship.";
        elseif ($intern === 'ongoing') $parts[] = "Currently doing an internship.";
        $parts[] = "Interested in {$interest}" . ($prefRole ? " ({$prefRole})." : ".");
        if ($ws1) $parts[] = $ws1 . ".";
        if ($ws2) $parts[] = "I want impact through " . $ws2 . ".";
        $text = trim(implode(' ', $parts));

        $stated = array_values(array_unique(array_merge(
            $this->toolsToSkills($tools),
            $this->subjectToSkills($programme . ' ' . $interest)
        )));

        // field of study from the stated programme name wins over the loose "interest" pick
        $parserFos = new \App\Services\ResumeParserService();
        $fos       = $parserFos->extractFieldOfStudy($programme);
        $domain    = $fos['domain'] ?? $this->normaliseDomain($interest);

        session()->set('stage', $stage);
        $verified = $intern === 'completed' ? 1 : 0;

        $this->buildProfile($text, $stated, $domain, session('profile')['animal'] ?? null, $verified, 'You');
        $this->injectCoreSkills($fos['coreSkills'] ?? []);
        $cand = $this->buildSignal(session('profile'));

        $svc         = new ScoreService();
        $bestRoleKey = (! empty($fos['role']) && isset(\App\Libraries\Catalog::roles()[$fos['role']]))
            ? $fos['role']
            : $this->bestRoleKey($cand, $svc, $domain);
        $bestRole  = \App\Libraries\Catalog::role($bestRoleKey);
        $bestM     = $svc->match($cand, $bestRole);
        $readiness = $svc->readiness($cand, $bestRole);
        $band      = $svc->risk($readiness['score']);

        $parser = new \App\Services\ResumeParserService();
        $rec    = new \App\Services\RecommendationService();
        $nrb    = new \App\Services\NoResumeProfileBuilderService();

        $animal = $parser->animalFromEvidence($cand['skills'], $text);

        $skillsOut = [];
        foreach ($cand['skills'] as $code => $s) {
            $skillsOut[] = ['label' => \App\Libraries\Catalog::label($code),
                            'source' => $s['source'] ?? 'inferred',
                            'conf' => round((($s['confidence'] ?? 1)) * 100)];
        }

        $resumeDraft = $nrb->resumeDraft([
            'name' => 'You', 'programme' => $programme, 'stage' => $stage, 'cgpa' => $cgpaRaw,
            'activities' => $activities, 'leadership' => $leadership, 'projects' => $projects,
            'tools' => $tools, 'competitions' => $comps, 'internship' => $intern,
            'interest' => $interest, 'role' => $prefRole,
        ], array_keys($cand['skills']), $domain);

        $firstProject  = $nrb->firstProject($domain, $bestM['gap']);
        $activitiesRec = $nrb->activities($domain);
        $courses       = $rec->microCourses($bestM['gap']);
        $nextAction    = $rec->nextAction($band, $bestM['gap'], $bestM['matched']);
        $cluster       = $parser->careerCluster($domain);

        // ---- Lumina Graph: enrich + learn ----
        $tax        = new \App\Services\TaxonomyService();
        $detCodes   = array_keys($cand['skills']);
        $graph      = $tax->enrich($detCodes, $programme, $domain);
        $tax->learn($detCodes, $text, $programme, $domain);
        $graphStats = $tax->stats();
        $novel      = $tax->extractNovel($text);
        $graphNew   = $tax->learnNovel($novel, $domain, $detCodes, $programme);
        $tax->reinforce(array_merge($detCodes, array_column($novel, 'code')));
        $graphStats = $tax->stats();

        // persist (demo-safe)
        $savedId = null;
        try {
            $sk   = session_id();
            $cgpa = is_numeric($cgpaRaw) ? (float) $cgpaRaw : null;
            $savedId = (new \App\Models\ResumeAnalysisModel())->insert([
                'session_key' => $sk, 'source' => 'no_resume', 'name' => 'You', 'raw_text' => $text,
                'target_domain' => $domain, 'career_cluster' => $cluster,
                'readiness' => $readiness['score'], 'employability_band' => $band,
                'animal_primary' => $animal['primary']['id'], 'animal_secondary' => $animal['secondary']['id'],
                'animal_growth' => $animal['growth']['id'],
                'skills_json' => json_encode($skillsOut),
                'projects_json' => json_encode($projects ? [$projects] : []),
                'leadership_json' => json_encode($leadership ? [$leadership] : []),
                'feedback_json' => json_encode([]), 'next_action' => $nextAction,
            ], true);
            (new \App\Models\CandidateProfileModel())->insert([
                'analysis_id' => $savedId, 'session_key' => $sk, 'name' => 'You', 'stage' => $stage,
                'programme' => $programme ?: null, 'cgpa' => $cgpa, 'target_domain' => $domain,
                'animal' => $animal['primary']['id'], 'verified' => $verified, 'evidence_text' => $text,
                'skills_json' => json_encode(array_keys($cand['skills'])), 'readiness' => $readiness['score'],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Lumina no-resume persist: ' . $e->getMessage());
        }

        return view('candidate/starter', [
            'title'         => 'Lumina · Starter Living Portfolio',
            'readiness'     => $readiness,
            'band'          => $band,
            'domain'        => $domain,
            'cluster'       => $cluster,
            'animal'        => $animal,
            'skills'        => $skillsOut,
            'gapLabels'     => \App\Libraries\Catalog::labels($bestM['gap']),
            'bestRole'      => $bestRole,
            'resumeDraft'   => $resumeDraft,
            'firstProject'  => $firstProject,
            'activitiesRec' => $activitiesRec,
            'courses'       => $courses,
            'nextAction'    => $nextAction,
            'graphRelated'  => $graph['related'],
            'graphStats'    => $graphStats,
            'graphNew'      => $graphNew,
        ]);
    }

    /** Map free-text tools to skill codes. */
    private function toolsToSkills(string $tools): array
    {
        $t = strtolower($tools); $out = [];
        $map = [
            'python' => 'python', 'sql' => 'sql', 'excel' => 'excel', 'spreadsheet' => 'excel',
            'tableau' => 'dashboarding', 'power bi' => 'dashboarding', 'powerbi' => 'dashboarding',
            'figma' => 'figma', 'photoshop' => 'graphic_design', 'illustrator' => 'graphic_design',
            'canva' => 'graphic_design', 'java' => 'java', 'javascript' => 'javascript',
            'react' => 'javascript', 'node' => 'javascript', 'html' => 'javascript', 'css' => 'javascript',
            'aws' => 'cloud', 'azure' => 'cloud', 'docker' => 'cloud', 'kubernetes' => 'cloud',
            'pandas' => 'data_analysis', 'numpy' => 'data_analysis', 'wordpress' => 'software', 'api' => 'api',
        ];
        foreach ($map as $kw => $code) { if (str_contains($t, $kw)) $out[] = $code; }
        return array_values(array_unique($out));
    }

    // ---------- Fasa 4 helpers ----------

    /** Best-fitting role per domain (Data/Engineering/Business), chosen dynamically from the shared Catalog. */
    private function pathRoles(?array $cand = null): array
    {
        $all = \App\Libraries\Catalog::roles();
        $svc = new ScoreService();
        $out = [];
        foreach (['Data', 'Engineering', 'Business'] as $domain) {
            $inDomain = array_values(array_filter($all, fn ($r) => $r['domain'] === $domain));
            $best = $inDomain[0] ?? null;
            if ($cand && count($inDomain) > 1) {
                $bestScore = -1;
                foreach ($inDomain as $r) {
                    $m = $svc->match($cand, $r)['matchScore'];
                    if ($m > $bestScore) { $bestScore = $m; $best = $r; }
                }
            }
            if ($best) {
                $out[$best['key']] = [
                    'key' => $best['key'], 'title' => $best['title'], 'domain' => $domain,
                    'color' => $best['color'], 'hex' => $best['hex'], 'required' => $best['required'],
                ];
            }
        }
        return $out;
    }

    private function skillLabel(string $c): string
    {
        $m = ['sql' => 'SQL', 'dashboarding' => 'Dashboarding', 'python' => 'Python', 'data_analysis' => 'Data Analysis',
              'software' => 'Software Dev', 'cloud' => 'Cloud', 'communication' => 'Communication',
              'stakeholder_mgmt' => 'Stakeholder Mgmt', 'leadership' => 'Leadership', 'budgeting' => 'Budgeting', 'teamwork' => 'Teamwork'];
        return $m[$c] ?? \App\Libraries\Catalog::label($c);
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
        // Model the 30/60/90 plan itself (coverage + evidence + activity + pace),
        // so the line rises even when few skill gaps remain — and matches the plan shown below.
        $now = $svc->readiness($cand, $role)['score'];

        // 30d — strengthen fundamentals: close the first skill gap
        $c30 = $cand;
        if (isset($gap[0])) { $c30['skills'][$gap[0]] = ['confidence' => 1.0, 'source' => 'stated']; }
        $d30 = $svc->readiness($c30, $role)['score'];

        // 60d — build a portfolio project: +1 project, +1 activity, close another gap
        $c60 = $c30;
        if (isset($gap[1])) { $c60['skills'][$gap[1]] = ['confidence' => 1.0, 'source' => 'stated']; }
        $c60['projects']   = ($c60['projects'] ?? 0) + 1;
        $c60['activities'] = ($c60['activities'] ?? 0) + 1;
        $d60 = $svc->readiness($c60, $role)['score'];

        // 90d — internship / verified evidence: verify, +1 project, close remaining gaps, faster pace
        $c90 = $c60;
        foreach ($gap as $g) { $c90['skills'][$g] = ['confidence' => 1.0, 'source' => 'stated']; }
        $c90['verified'] = 1;
        $c90['projects'] = ($c90['projects'] ?? 0) + 1;
        $c90['pace']     = 'Fast';
        $d90 = $svc->readiness($c90, $role)['score'];

        // never dip: enforce monotonic non-decreasing for a clean trajectory
        $d30 = max($now, $d30); $d60 = max($d30, $d60); $d90 = max($d60, $d90);

        return ['now' => $now, 'd30' => $d30, 'd60' => $d60, 'd90' => $d90];
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
