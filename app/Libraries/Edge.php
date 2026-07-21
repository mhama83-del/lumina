<?php

namespace App\Libraries;

/**
 * Lumina EDGE 2.0 — evidence-based employability signals.
 * Menggantikan Work Animal / animal-fit dalam aliran baharu.
 *
 * Prinsip: Decision support only — people decide.
 * Survey menghasilkan hanya status 'stated' (self-reported approach).
 * Ia TIDAK menghasilkan skor, personaliti, atau label manusia.
 * Evidence (resume/SAOL) yang menaikkan status ke Inferred/Supported (Fasa 2+).
 */
class Edge
{
    /** Lima signal EDGE universal — untuk semua role family. */
    public static function signals(): array
    {
        return [
            'reasoning_judgement' => [
                'key'   => 'reasoning_judgement',
                'name'  => 'Reasoning & Judgement',
                'short' => 'How you understand problems, use information and choose the next step.',
                'facets' => ['clarify problem', 'compare options', 'use evidence', 'explain a decision'],
                'color' => 'var(--indigo)',
                'hex'   => '#6C5CE7',
            ],
            'delivery_reliability' => [
                'key'   => 'delivery_reliability',
                'name'  => 'Delivery & Reliability',
                'short' => 'How you plan, finish, check and keep the quality of your work.',
                'facets' => ['plan/prioritise', 'follow through', 'quality/safety check', 'manage risk'],
                'color' => 'var(--teal)',
                'hex'   => '#14B8A6',
            ],
            'collaboration_communication' => [
                'key'   => 'collaboration_communication',
                'name'  => 'Collaboration & Communication',
                'short' => 'How you coordinate work and share ideas with others.',
                'facets' => ['coordinate', 'listen/respond', 'adapt explanation', 'team update'],
                'color' => 'var(--gold)',
                'hex'   => '#FDE047',
            ],
            'learning_adaptation' => [
                'key'   => 'learning_adaptation',
                'name'  => 'Learning & Adaptation',
                'short' => 'How you learn, take feedback and adjust your approach.',
                'facets' => ['learn tool/process', 'use feedback', 'iterate', 'adapt to change'],
                'color' => '#38BDF8',
                'hex'   => '#38BDF8',
            ],
            'initiative_ownership' => [
                'key'   => 'initiative_ownership',
                'name'  => 'Initiative & Ownership',
                'short' => 'How you take sensible action and own the outcome.',
                'facets' => ['notice opportunity', 'take ownership', 'improve process', 'lead at scale'],
                'color' => '#FB923C',
                'hex'   => '#FB923C',
            ],
        ];
    }

    /** Susunan tetap 5 signal (untuk spider paksi & kad). */
    public static function order(): array
    {
        return [
            'reasoning_judgement',
            'delivery_reliability',
            'collaboration_communication',
            'learning_adaptation',
            'initiative_ownership',
        ];
    }

    /**
     * Bank soalan — Set 1 (13 soalan seimbang).
     * Struktur: 3 Reasoning / 3 Delivery / 3 Collaboration / 2 Learning / 2 Initiative.
     * Campuran reflection + micro-SJT.
     */
    public static function bank(): array
    {
        return [
            'set1' => [
                ['signal' => 'reasoning_judgement', 'type' => 'reflection',
                 'q' => 'When a task is unclear, what is usually your first step?',
                 'opts' => [
                     'Ask a question to clarify what is really needed',
                     'Break it into smaller parts I can start on',
                     'Look for a similar example to learn from',
                     'Sketch a rough plan and adjust as I go',
                 ]],
                ['signal' => 'reasoning_judgement', 'type' => 'reflection',
                 'q' => 'When two options both look reasonable, what do you compare first?',
                 'opts' => [
                     'Which one solves the real problem better',
                     'Which one is more practical with the time I have',
                     'What could go wrong with each',
                     'What others affected would prefer',
                 ]],
                ['signal' => 'reasoning_judgement', 'type' => 'sjt',
                 'q' => 'Two days before a presentation, one data point conflicts with another source. What is your first step?',
                 'opts' => [
                     'Check which source is more reliable and why',
                     'Recheck how I calculated or read the numbers',
                     'Ask someone who knows the data well',
                     'Note the conflict clearly and present both',
                 ]],
                ['signal' => 'delivery_reliability', 'type' => 'reflection',
                 'q' => 'When several deadlines overlap, how do you begin organising the work?',
                 'opts' => [
                     'List everything, then order by what matters most',
                     'Start with what blocks other people',
                     'Handle quick wins first to reduce the pile',
                     'Ask which deadline has the least room to move',
                 ]],
                ['signal' => 'delivery_reliability', 'type' => 'reflection',
                 'q' => 'Before you share or submit work, what do you usually check?',
                 'opts' => [
                     'That it actually answers what was asked',
                     'That there are no obvious mistakes',
                     'That someone else can follow it',
                     'That I met the requirements given',
                 ]],
                ['signal' => 'delivery_reliability', 'type' => 'sjt',
                 'q' => 'A dependency is likely to delay an important deadline. What is your first step?',
                 'opts' => [
                     'Flag it early to the people affected',
                     'Look for a way to work around it',
                     'Re-plan what can still be done meanwhile',
                     'Check how much the deadline can flex',
                 ]],
                ['signal' => 'collaboration_communication', 'type' => 'reflection',
                 'q' => 'If a group is not aligned, what is the first action most like you?',
                 'opts' => [
                     'Restate the shared goal so we agree on it',
                     'Ask each person what they see differently',
                     'Suggest a small step everyone can accept',
                     'Write down what we decided so far',
                 ]],
                ['signal' => 'collaboration_communication', 'type' => 'reflection',
                 'q' => 'How would you explain technical work to someone less familiar with it?',
                 'opts' => [
                     'Use a simple everyday comparison',
                     'Start from why it matters to them',
                     'Show a small example instead of theory',
                     'Check what they already know first',
                 ]],
                ['signal' => 'collaboration_communication', 'type' => 'sjt',
                 'q' => 'Two team members think they own the same task. What would you do?',
                 'opts' => [
                     'Get them talking to agree who leads',
                     'Clarify the split so both parts are covered',
                     'Raise it with whoever assigned the work',
                     'Suggest they pair up on it together',
                 ]],
                ['signal' => 'learning_adaptation', 'type' => 'reflection',
                 'q' => 'When feedback challenges your first idea, what do you do next?',
                 'opts' => [
                     'Ask for a specific example to understand it',
                     'Try the suggestion and compare results',
                     'Keep what works and adjust the rest',
                     'Take time to think before responding',
                 ]],
                ['signal' => 'learning_adaptation', 'type' => 'sjt',
                 'q' => 'You need a new tool or process this week. How would you start?',
                 'opts' => [
                     'Find a short tutorial and try a small task',
                     'Ask someone who already uses it',
                     'Read the basics, then learn by doing',
                     'Look for an example close to my case',
                 ]],
                ['signal' => 'initiative_ownership', 'type' => 'reflection',
                 'q' => 'If you notice a small problem affecting a group, what would you do?',
                 'opts' => [
                     'Fix it if it is within what I can do',
                     'Tell whoever can act on it',
                     'Suggest a simple improvement',
                     'Check if others noticed it too',
                 ]],
                ['signal' => 'initiative_ownership', 'type' => 'sjt',
                 'q' => 'You see a useful improvement but nobody owns it. What is an appropriate first action?',
                 'opts' => [
                     'Propose it to the right person',
                     'Try a small version to show it works',
                     'Ask if I can take it on',
                     'Check why it has not been done yet',
                 ]],
            ],
        ];
    }

    /** Pulangkan satu set soalan mengikut key (default set1). */
    public static function items(string $set = 'set1'): array
    {
        $bank = self::bank();
        return $bank[$set] ?? $bank['set1'];
    }

    /**
     * Observe: tukar responses survey → status signal.
     * Survey SAHAJA = 'stated' (spesifikasi hard rule).
     */
    public static function observe(array $responses, array $items): array
    {
        $signals = [];
        foreach (self::order() as $key) {
            $signals[$key] = ['status' => 'needs', 'responses' => []];
        }

        foreach ($responses as $i => $raw) {
            if (! isset($items[$i])) continue;
            $sig = $items[$i]['signal'] ?? null;
            if (! $sig || ! isset($signals[$sig])) continue;

            $choice = $raw;
            if (is_string($raw) && strpos($raw, ':') !== false) {
                $parts  = explode(':', $raw);
                $choice = $parts[1] ?? '';
            }
            if ($choice === 'skip' || $choice === '') continue;

            $signals[$sig]['responses'][] = [
                'q'      => $items[$i]['q'] ?? '',
                'choice' => (int) $choice,
                'type'   => $items[$i]['type'] ?? 'reflection',
            ];
            $signals[$sig]['status'] = 'stated';
        }

        return $signals;
    }

    /** Jawapan demo 3 persona (format "qi:oi", 13 soalan Set 1). */
    public static function demoResponses(string $persona): array
    {
        $demo = [
            'aiman'  => ['0:0','1:0','2:0','3:1','4:1','5:0','6:1','7:0','8:2','9:2','10:0','11:0','12:3'],
            'nurul'  => ['0:2','1:2','2:2','3:2','4:2','5:0','6:3','7:3','8:2','9:2','10:1','11:1','12:1'],
            'weijie' => ['0:1','1:1','2:1','3:0','4:0','5:3','6:2','7:2','8:2','9:2','10:2','11:3','12:2'],
        ];
        return $demo[$persona] ?? $demo['aiman'];
    }

    /** Peta skill code -> EDGE signal (rujuk facet spesifikasi). Satu skill boleh >1 signal. */
    public static function mapSkillToSignal(string $code): array
    {
        static $map = [
            'data_analysis'=>['reasoning_judgement'], 'dashboarding'=>['reasoning_judgement','delivery_reliability'],
            'statistics'=>['reasoning_judgement'], 'python'=>['reasoning_judgement','delivery_reliability'],
            'sql'=>['reasoning_judgement'], 'machine_learning'=>['reasoning_judgement'],
            'research'=>['reasoning_judgement'], 'field_research'=>['reasoning_judgement'],
            'scientific_writing'=>['reasoning_judgement','collaboration_communication'],
            'policy_analysis'=>['reasoning_judgement'], 'legal_research'=>['reasoning_judgement'],
            'risk_modeling'=>['reasoning_judgement','delivery_reliability'],
            'lab_techniques'=>['reasoning_judgement','delivery_reliability'], 'excel'=>['reasoning_judgement'],
            'finance'=>['reasoning_judgement'], 'accounting'=>['reasoning_judgement','delivery_reliability'],
            'audit'=>['reasoning_judgement','delivery_reliability'],
            'software'=>['delivery_reliability'], 'java'=>['delivery_reliability'],
            'javascript'=>['delivery_reliability'], 'api'=>['delivery_reliability'], 'cloud'=>['delivery_reliability'],
            'project_mgmt'=>['delivery_reliability','collaboration_communication'],
            'project_planning'=>['delivery_reliability'], 'quality_control'=>['delivery_reliability'],
            'process_safety'=>['delivery_reliability'], 'six_sigma'=>['delivery_reliability'],
            'manufacturing'=>['delivery_reliability'], 'logistics'=>['delivery_reliability'],
            'budgeting'=>['delivery_reliability'], 'circuit_design'=>['delivery_reliability','reasoning_judgement'],
            'electrical_schematics'=>['delivery_reliability','reasoning_judgement'],
            'embedded'=>['delivery_reliability','reasoning_judgement'], 'plc'=>['delivery_reliability'],
            'control_systems'=>['delivery_reliability','reasoning_judgement'],
            'mechanical_design'=>['delivery_reliability','reasoning_judgement'],
            'structural_engineering'=>['delivery_reliability','reasoning_judgement'],
            'chemical_processing'=>['delivery_reliability'], 'cad'=>['delivery_reliability'],
            'aerodynamics'=>['delivery_reliability','reasoning_judgement'], 'aircraft_systems'=>['delivery_reliability'],
            'avionics'=>['delivery_reliability'], 'robotics'=>['delivery_reliability','reasoning_judgement'],
            'crop_science'=>['delivery_reliability'], 'aquaculture'=>['delivery_reliability'],
            'animal_care'=>['delivery_reliability'], 'clinical_skills'=>['delivery_reliability'],
            'patient_care'=>['delivery_reliability','collaboration_communication'],
            'pharmacology'=>['delivery_reliability','reasoning_judgement'],
            'rehabilitation'=>['delivery_reliability','collaboration_communication'],
            'public_health'=>['delivery_reliability','reasoning_judgement'],
            'hospitality_ops'=>['delivery_reliability','collaboration_communication'],
            'communication'=>['collaboration_communication'], 'presentation'=>['collaboration_communication'],
            'stakeholder_mgmt'=>['collaboration_communication'], 'teamwork'=>['collaboration_communication'],
            'customer_service'=>['collaboration_communication'], 'counselling'=>['collaboration_communication'],
            'case_mgmt'=>['collaboration_communication','delivery_reliability'],
            'community_outreach'=>['collaboration_communication','initiative_ownership'],
            'writing'=>['collaboration_communication'], 'content'=>['collaboration_communication'],
            'translation'=>['collaboration_communication'], 'interviewing'=>['collaboration_communication'],
            'sales'=>['collaboration_communication','initiative_ownership'],
            'marketing'=>['collaboration_communication','initiative_ownership'],
            'social_media'=>['collaboration_communication'], 'seo'=>['collaboration_communication','reasoning_judgement'],
            'event_planning'=>['collaboration_communication','delivery_reliability'],
            'stage_presence'=>['collaboration_communication'], 'performance'=>['collaboration_communication'],
            'teaching'=>['learning_adaptation','collaboration_communication'],
            'lesson_planning'=>['learning_adaptation','delivery_reliability'],
            'classroom_mgmt'=>['learning_adaptation','collaboration_communication'],
            'curriculum_design'=>['learning_adaptation','delivery_reliability'],
            'training_design'=>['learning_adaptation','delivery_reliability'],
            'leadership'=>['initiative_ownership','collaboration_communication'],
            'community'=>['initiative_ownership','collaboration_communication'],
            'entrepreneurship'=>['initiative_ownership'], 'innovation'=>['initiative_ownership','reasoning_judgement'],
            'sustainability'=>['initiative_ownership','reasoning_judgement'],
            'design_thinking'=>['reasoning_judgement','collaboration_communication'],
            'ui_ux'=>['collaboration_communication','delivery_reliability'], 'figma'=>['delivery_reliability'],
            'graphic_design'=>['delivery_reliability','collaboration_communication'],
            'archival_work'=>['delivery_reliability','reasoning_judgement'],
        ];
        return $map[$code] ?? [];
    }

    /** Urutan status: needs < stated < inferred < supported. */
    public static function statusRank(string $status): int
    {
        $rank = ['needs' => 0, 'stated' => 1, 'inferred' => 2, 'supported' => 3];
        return $rank[$status] ?? 0;
    }

    /**
     * Infer signal daripada evidence (skills dari inferSkillsExplained).
     * Setiap skill inferred -> signal(s) dengan status 'inferred' + quote.
     * @param array $skills  [code => ['confidence','source','from']]
     * @return array  [signal_key => ['status'=>'inferred','evidence'=>[['quote','skill','source']]]]
     */
    public static function inferFromEvidence(array $skills): array
    {
        $out = [];
        foreach (self::order() as $key) { $out[$key] = ['status' => 'needs', 'evidence' => []]; }
        foreach ($skills as $code => $meta) {
            $signals = self::mapSkillToSignal($code);
            if (! $signals) continue;
            $src   = $meta['source'] ?? 'inferred';
            $quote = $meta['from'] ?? null;
            // survey-stated skills (source=stated, from=null) -> stated; inferred -> inferred
            $st = ($src === 'stated') ? 'stated' : 'inferred';
            foreach ($signals as $sig) {
                if (! isset($out[$sig])) continue;
                $out[$sig]['evidence'][] = ['quote' => $quote, 'skill' => $code, 'source' => $src];
                if (self::statusRank($st) > self::statusRank($out[$sig]['status'])) {
                    $out[$sig]['status'] = $st;
                }
            }
        }
        return $out;
    }

    /**
     * Gabung observasi survey (stated) + evidence (inferred). Status tertinggi menang.
     * @return array  [signal_key => ['status','responses','evidence','name','short']]
     */
    public static function merge(array $observed, array $inferred): array
    {
        $sigDefs = self::signals();
        $out = [];
        foreach (self::order() as $key) {
            $o = $observed[$key] ?? ['status' => 'needs', 'responses' => []];
            $e = $inferred[$key] ?? ['status' => 'needs', 'evidence' => []];
            $status = (self::statusRank($e['status']) >= self::statusRank($o['status']))
                ? $e['status'] : $o['status'];
            $out[$key] = [
                'key'       => $key,
                'name'      => $sigDefs[$key]['name'] ?? $key,
                'short'     => $sigDefs[$key]['short'] ?? '',
                'status'    => $status,
                'responses' => $o['responses'] ?? [],
                'evidence'  => $e['evidence'] ?? [],
            ];
        }
        return $out;
    }

    /**
     * Tukar edge[signal=>evidence] jadi senarai rata untuk table Review.
     * Satu quote unik = satu baris; quote yang menyokong >1 signal digabung.
     * @return array  [['id','quote','signals'=>[keys],'source','skill']]
     */
    public static function suggestions(array $edge): array
    {
        $byQuote = [];
        foreach ($edge as $sigKey => $sig) {
            if (! is_array($sig) || empty($sig['evidence'])) continue;
            foreach ($sig['evidence'] as $ev) {
                $quote = $ev['quote'] ?? '';
                if ($quote === '' || $quote === null) continue;
                if (! isset($byQuote[$quote])) {
                    $byQuote[$quote] = ['quote' => $quote, 'signals' => [],
                        'source' => $ev['source'] ?? 'inferred', 'skill' => $ev['skill'] ?? ''];
                }
                if (! in_array($sigKey, $byQuote[$quote]['signals'], true)) {
                    $byQuote[$quote]['signals'][] = $sigKey;
                }
            }
        }
        $out = []; $i = 1;
        foreach ($byQuote as $item) { $item['id'] = $i++; $out[] = $item; }
        return $out;
    }

    /** Nama pendek signal untuk chip table. */
    public static function signalName(string $key): string
    {
        $s = self::signals();
        return $s[$key]['name'] ?? $key;
    }

    /** Cadangan Add per persona (demo licin — juri tak perlu fikir isi apa). */
    public static function addSuggestions(string $persona): array
    {
        $s = [
            'aiman' => [
                ['quote'=>'built a dashboard used by 5 lecturers', 'signals'=>['delivery_reliability','reasoning_judgement']],
                ['quote'=>'taught Python to 30 secondary students', 'signals'=>['learning_adaptation','collaboration_communication']],
            ],
            'nurul' => [
                ['quote'=>'organised a campus event for 200 people', 'signals'=>['collaboration_communication','initiative_ownership']],
                ['quote'=>'grew a client Instagram following from 400 to 3200', 'signals'=>['initiative_ownership','delivery_reliability']],
            ],
            'weijie' => [
                ['quote'=>'designed a PCB from schematic to layout', 'signals'=>['delivery_reliability','reasoning_judgement']],
                ['quote'=>'reduced lab equipment idle power by 22%', 'signals'=>['delivery_reliability','initiative_ownership']],
            ],
            'self' => [
                ['quote'=>'led a small team project to completion', 'signals'=>['initiative_ownership','delivery_reliability']],
                ['quote'=>'presented findings to a group', 'signals'=>['collaboration_communication']],
            ],
        ];
        return $s[$persona] ?? $s['self'];
    }

    /** Hint Edit — tunjuk SAOL (outcome/learning naik ke Supported). */
    public static function editHints(): array
    {
        return [
            'outcome'  => 'Add the outcome — what changed or improved because of it?',
            'learning' => 'Add what you learned or did better afterwards.',
            'signal'   => 'You can also change which work signal this links to.',
        ];
    }

    /** Label status kanonik untuk paparan. */
    public static function statusLabel(string $status): string
    {
        $m = ['needs'=>'Needs Evidence','stated'=>'Stated','inferred'=>'Inferred','supported'=>'Supported'];
        return $m[$status] ?? 'Needs Evidence';
    }

    /**
     * Data spider: 5 paksi + ordinal 0-3 (needs..supported) untuk render SVG.
     * Ordinal DALAMAN sahaja — tidak dipapar sebagai nombor/peratus (spesifikasi).
     */
    /** Bank soalan v2 (opsyen -> domain). R6. */
    public static function itemsV2(): array
    {
        return include __DIR__ . '/edge_bank_v2.php';
    }

    /** Skor survey: 13 jawapan (qi:oi) -> skor 5 domain (0-10). R6. */
    public static function surveyScore(array $responses): array
    {
        $bank = self::itemsV2();
        $raw = array_fill_keys(self::order(), 0);
        foreach ($responses as $r) {
            // format 'qi:oi'
            if (strpos($r, ':') === false) continue;
            [$qi, $oi] = explode(':', $r, 2);
            if ($oi === 'skip' || ! isset($bank[(int)$qi]['opts'][(int)$oi])) continue;
            $sig = $bank[(int)$qi]['opts'][(int)$oi]['sig'] ?? null;
            if ($sig && isset($raw[$sig])) $raw[$sig]++;
        }
        $out = [];
        foreach (self::order() as $sig) { $out[$sig] = min(10, $raw[$sig] * 2); }
        return $out;
    }

    /** Coverage bukti: bilangan skill per domain (0-10). R6. */
    public static function evidenceCoverage(array $skills): array
    {
        $count = array_fill_keys(self::order(), 0);
        foreach ($skills as $code => $meta) {
            foreach (self::mapSkillToSignal($code) as $sig) {
                if (isset($count[$sig])) $count[$sig]++;
            }
        }
        foreach ($count as $sig => $c) { $count[$sig] = min(10, $c); }
        return $count;
    }

    /** Spider dua lapisan: survey (kelabu) + evidence (indigo). 0-10. R6. */
    public static function spiderDual(array $survey, array $evidence): string
    {
        $ORDER = self::order(); $n = count($ORDER);
        $cx = 175; $cy = 178; $R = 120;
        $short = ['reasoning_judgement'=>'Reasoning','delivery_reliability'=>'Delivery',
            'collaboration_communication'=>'Collaboration','learning_adaptation'=>'Learning',
            'initiative_ownership'=>'Initiative'];
        $mkpts = function($scores) use ($ORDER,$n,$cx,$cy,$R) {
            $p=[]; foreach ($ORDER as $i=>$sig) { $ang=-M_PI/2+($i*2*M_PI/$n); $r=(($scores[$sig]??0)/10)*$R; $p[]=[$cx+$r*cos($ang),$cy+$r*sin($ang)]; } return $p;
        };
        $svg = '<svg viewBox="-60 0 470 380" class="edge-spider" role="img" aria-label="EDGE Evidence Map">';
        for ($lvl=2; $lvl<=10; $lvl+=2) {
            $ring=[]; foreach ($ORDER as $i=>$sig){$ang=-M_PI/2+($i*2*M_PI/$n);$r=($lvl/10)*$R;$ring[]=[$cx+$r*cos($ang),$cy+$r*sin($ang)];}
            $d=''; foreach($ring as $k=>$p){$d.=($k?'L':'M').round($p[0],1).' '.round($p[1],1).' ';}
            $svg.='<path d="'.$d.'Z" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="1"/>';
        }
        foreach ($ORDER as $i=>$sig){$ang=-M_PI/2+($i*2*M_PI/$n);$svg.='<line x1="'.$cx.'" y1="'.$cy.'" x2="'.round($cx+$R*cos($ang),1).'" y2="'.round($cy+$R*sin($ang),1).'" stroke="rgba(255,255,255,.06)"/>';}
        $sp=$mkpts($survey); $sd=''; foreach($sp as $k=>$p){$sd.=($k?'L':'M').round($p[0],1).' '.round($p[1],1).' ';}
        $svg.='<path d="'.$sd.'Z" fill="rgba(148,163,184,.10)" stroke="#94a3b8" stroke-width="1.5" stroke-dasharray="4 3"/>';
        $ep=$mkpts($evidence); $ed=''; foreach($ep as $k=>$p){$ed.=($k?'L':'M').round($p[0],1).' '.round($p[1],1).' ';}
        $svg.='<path d="'.$ed.'Z" fill="rgba(108,92,231,.25)" stroke="#6C5CE7" stroke-width="2.5"/>';
        foreach ($ep as $p){$svg.='<circle cx="'.round($p[0],1).'" cy="'.round($p[1],1).'" r="4" fill="#6C5CE7"/>';}
        foreach ($ORDER as $i=>$sig){$ang=-M_PI/2+($i*2*M_PI/$n);$lx=$cx+($R+22)*cos($ang);$ly=$cy+($R+22)*sin($ang);$ca=cos($ang);$anc=abs($ca)<0.3?'middle':($ca>0?'start':'end');$svg.='<text x="'.round($lx,1).'" y="'.round($ly+3,1).'" fill="#cbd5e1" font-size="11" text-anchor="'.$anc.'">'.($short[$sig]??$sig).'</text>';}
        return $svg . '</svg>';
    }

    /** Ayat approach ikut skor survey. R6. */
    public static function surveyPhrase(string $sig, int $score): string
    {
        $a = ['reasoning_judgement'=>'structured problem-solving','delivery_reliability'=>'planning and following through',
            'collaboration_communication'=>'working with and through people','learning_adaptation'=>'learning and adapting',
            'initiative_ownership'=>'taking initiative and ownership'];
        $ap = $a[$sig] ?? 'this area';
        if ($score >= 8) return "Your answers strongly lean towards $ap.";
        if ($score >= 4) return "Your answers show some leaning towards $ap.";
        if ($score >= 1) return "Your answers touch on $ap occasionally.";
        return "Your answers did not emphasise $ap.";
    }

    /** Ayat evidence ikut bilangan bukti. R6. */
    public static function evidencePhrase(int $count): string
    {
        if ($count >= 6) return "Your CV shows strong evidence here ($count examples on record).";
        if ($count >= 3) return "Your CV shows some evidence here ($count examples on record).";
        if ($count >= 1) return 'Your CV shows early evidence here (' . $count . ' example' . ($count>1?'s':'') . ' on record).';
        return 'No evidence found in your CV yet for this signal.';
    }

    /** Insight gap survey vs evidence. R6. */
    public static function gapInsight(int $survey, int $evidence): string
    {
        if ($survey >= 6 && $evidence <= 2) return 'You feel strong here, but your CV shows little proof. Adding an example would strengthen it.';
        if ($evidence >= 6 && $survey <= 2) return 'Your CV proves this well, even though your answers did not emphasise it.';
        if ($survey >= 4 && $evidence >= 4) return 'Your approach and your evidence align well here.';
        if ($survey <= 2 && $evidence <= 2) return 'A useful area to build with a concrete example.';
        return '';
    }

    /** Cards v2: evidence list + survey analysis per domain. R6. */
    public static function cardsV2(array $survey, array $evidence, array $evidenceQuotes): array
    {
        $defs = self::signals(); $out = [];
        foreach (self::order() as $sig) {
            $sv = $survey[$sig] ?? 0; $ev = $evidence[$sig] ?? 0;
            $quotes = array_slice(array_values(array_unique($evidenceQuotes[$sig] ?? [])), 0, 4);
            $out[] = ['key'=>$sig, 'name'=>$defs[$sig]['name'] ?? $sig,
                'hex'=>$defs[$sig]['hex'] ?? '#6C5CE7',
                'survey'=>$sv, 'evidence'=>$ev,
                'surveyPhrase'=>self::surveyPhrase($sig, $sv),
                'evidencePhrase'=>self::evidencePhrase($ev),
                'gap'=>self::gapInsight($sv, $ev),
                'quotes'=>$quotes];
        }
        return $out;
    }

    /** Jana HTML cards v2: survey analysis + evidence list. R6. */
    public static function cardsV2HTML(array $survey, array $evidence, array $evidenceQuotes): string
    {
        $html = '<div class="edge-cards-grid">';
        $__ci = 0;
        foreach (self::cardsV2($survey, $evidence, $evidenceQuotes) as $c) {
            $hex = $c['hex'];
            $__hid = $__ci > 0 ? ' edge-card-hidden' : '';
            $html .= '<div class="edge-card'.$__hid.'" data-sig="'.$c['key'].'" style="border-left:3px solid '.$hex.'">';
            $__ci++;
            $html .= '<div class="edge-card-head"><span class="edge-card-name" style="color:'.$hex.'">'.esc($c['name']).'</span></div>';
            // baris skor: survey vs evidence
            $html .= '<div class="edge-scorebar">';
            $html .= '<span class="edge-sb-label">Approach</span><span class="edge-sb-track"><span class="edge-sb-fill edge-sb-survey" style="width:'.($c['survey']*10).'%"></span></span>';
            $html .= '<span class="edge-sb-label">Evidence</span><span class="edge-sb-track"><span class="edge-sb-fill edge-sb-evidence" style="width:'.($c['evidence']*10).'%;background:'.$hex.'"></span></span>';
            $html .= '</div>';
            $html .= '<p class="edge-card-insight">'.esc($c['surveyPhrase']).' '.esc($c['evidencePhrase']).'</p>';
            if ($c['gap']) { $html .= '<p class="edge-card-gap">'.esc($c['gap']).'</p>'; }
            if (! empty($c['quotes'])) {
                $html .= '<div class="edge-card-map"><div class="edge-map-title">Found in your CV:</div>';
                foreach ($c['quotes'] as $q) {
                    $html .= '<div class="edge-branch"><span class="edge-branch-line" style="background:'.$hex.'"></span>';
                    $html .= '<span class="edge-branch-node">"'.esc($q).'"</span></div>';
                }
                $html .= '</div>';
            }
            // Grow: teks galakan untuk paksi bukti rendah (bukan link — review dibuat di /resume).
            if ($c['evidence'] < 3) {
                $html .= '<p class="edge-card-next">A useful example to add in your next update.</p>';
            }
            $html .= '</div>';
        }
        return $html . '</div>';
    }

    /** Kumpul petikan bukti per domain dari RAW skills (guna 'from'). R6. */
    public static function evidenceQuotes(array $skills, string $cvText = ''): array
    {
        $out = array_fill_keys(self::order(), []);
        foreach ($skills as $code => $meta) {
            $kw = $meta['from'] ?? $code;
            if (! $kw) continue;
            $quote = $cvText ? self::enrichQuote($kw, $cvText) : $kw;
            foreach (self::mapSkillToSignal($code) as $sig) {
                if (isset($out[$sig])) $out[$sig][] = $quote;
            }
        }
        // unik per domain
        foreach ($out as $sig => $q) { $out[$sig] = array_values(array_unique($q)); }
        return $out;
    }

    /** Perkaya keyword jadi frasa konteks dari teks CV (hormati sempadan ayat). R6/B2. */
    public static function enrichQuote(string $keyword, string $cvText): string
    {
        if ($keyword === '' || $cvText === '') return $keyword;
        $sentences = preg_split('/[.;\n]+/', $cvText);
        foreach ($sentences as $sent) {
            if (stripos($sent, $keyword) !== false) {
                $sent = trim($sent);
                $words = preg_split('/\s+/', $sent);
                if (count($words) <= 9) return $sent;
                $ki = 0;
                foreach ($words as $i => $w) { if (stripos($w, $keyword) !== false) { $ki = $i; break; } }
                $from = max(0, $ki - 3);
                return implode(' ', array_slice($words, $from, 8));
            }
        }
        return $keyword;
    }

}
