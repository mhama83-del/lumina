<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Continuum\CorePolicy\Service\DemoIdentityRegistry;
use Continuum\Applications\Domain\ConsentPreview;

/**
 * Idempotent demo seeder (08 fixture procedure, 11 demo scenarios).
 * All rows carry data_classification = synthetic_fixture. The named scenarios are curated;
 * background scale rows (1,000+) are generated separately (see generateScaleFixtures()).
 * NEVER labels synthetic data as real Talentbank/employer/market data.
 */
class ContinuumDemoSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $db  = $this->db;
        $cp  = new ConsentPreview();

        foreach ([
            'application_events','applications','availability_pulses','consent_snapshots',
            'evidence_links','evidence_sources','evidence_claims','survey_responses',
            'role_requirements','role_versions','roles','taxonomy_relations','taxonomy_skills',
            'intervention_cases','cohorts','mentor_requests','feedback_records','human_reviews','candidates',
        ] as $t) {
            $db->table($t)->truncate();
        }

        // --- Candidates (10 from the persona registry) ---
        foreach (DemoIdentityRegistry::all() as $key => $p) {
            if ($p['role']->value !== 'candidate') { continue; }
            $db->table('candidates')->insert([
                'id'                  => $p['subject'],
                'identity_key'        => $key,
                'display_name'        => $p['name'],
                'discipline'          => $p['discipline'],
                'profile_reference_id'=> 'MOCK-PASSPORT-' . str_pad((string) $p['subject'], 4, '0', STR_PAD_LEFT),
                'availability_state'  => $p['subject'] === 7 ? 'unavailable' : 'actively_available',
                'data_classification' => 'synthetic_fixture',
                'created_at'          => $now,
            ]);
        }

        // --- Taxonomy skills (extended catalogue) ---
        $skills = [
            'sql'=>'SQL','python'=>'Python','dashboarding'=>'Dashboarding','stakeholder_comm'=>'Stakeholder Communication',
            'data_modelling'=>'Data Modelling','excel'=>'Excel & Spreadsheets','forecasting'=>'Forecasting',
            'process_mapping'=>'Process Mapping','user_research'=>'User Research','javascript'=>'JavaScript','testing'=>'Automated Testing',
        ];
        $skillId = []; $i = 1;
        foreach ($skills as $code=>$label) {
            $db->table('taxonomy_skills')->insert(['id'=>$i,'code'=>$code,'label'=>$label,'domain'=>'general']);
            $skillId[$code] = $i; $i++;
        }
        $db->table('taxonomy_relations')->insert([
            'from_skill'=>$skillId['data_modelling'],'to_skill'=>$skillId['sql'],
            'relation'=>'related_to','confidence'=>0.60,'source'=>'curated_seed','status'=>'approved',
        ]);

        // ---- Helper: add an evidence claim (+ optional approved source) ----
        $bag = []; // candidateId => list of claim dicts for consent building
        $addEv = function (int $cand, string $signal, string $label, string $skill, string $text, ?string $src=null, ?string $verifier=null)
                 use ($db, $now, &$bag, $skillId): int {
            $db->table('evidence_claims')->insert([
                'candidate_id'=>$cand,'signal'=>$signal,'label'=>$label,'claim_text'=>$text,
                'confirmation_state'=>'confirmed','taxonomy_skill_id'=>$skillId[$skill],'created_at'=>$now,
            ]);
            $cid = (int) $db->insertID();
            $excerpt = null;
            if ($src !== null) {
                $db->table('evidence_sources')->insert([
                    'candidate_id'=>$cand,'type'=>'link','locator'=>'https://example.org/ev/'.$cid,
                    'excerpt'=>$src,'added_by'=>'candidate','consentable'=>1,'created_at'=>$now,
                ]);
                $sid = (int) $db->insertID();
                $db->table('evidence_links')->insert([
                    'claim_id'=>$cid,'source_id'=>$sid,'rationale'=>'Source supports the claim',
                    'provenance'=>'candidate_approved','verified_by'=>$verifier,'created_at'=>$now,
                ]);
                $excerpt = $src;
            }
            $bag[$cand][] = ['id'=>$cid,'signal'=>$signal,'label'=>$label,'claim_text'=>$text,'source_excerpt'=>$excerpt,'requirement'=>null];
            return $cid;
        };

        // ---- Candidate evidence ----
        // c01 amina — strong, but ONE Critical SQL source gap (the live demo moment).
        $addEv(1,'delivery_reliability','supported','dashboarding','Built a weekly sales dashboard used by the regional team','Weekly regional sales dashboard (synthetic sample).');
        $addEv(1,'delivery_reliability','stated','sql','Wrote SQL to segment customers for the dashboard'); // Stated only
        // c02 daniel — SWE, one human-verified.
        $addEv(2,'delivery_reliability','human_verified','javascript','Shipped a checkout module in a team of four','Merged PR #482, reviewed and approved.','e02_apex');
        $addEv(2,'reasoning_judgement','supported','testing','Added integration tests that cut regressions','CI test suite screenshot (synthetic).');
        $addEv(2,'delivery_reliability','stated','sql','Queried the orders table for a reporting task');
        // c03 siti — UX.
        $addEv(3,'learning_adaptation','supported','user_research','Ran 8 usability interviews and reframed onboarding','Interview synthesis deck (synthetic).');
        $addEv(3,'collaboration_communication','stated','stakeholder_comm','Presented findings to product and engineering');
        // c04 aaron — Finance.
        $addEv(4,'delivery_reliability','supported','excel','Built a rolling cash-flow model for three business units','Cash-flow workbook (synthetic sample).');
        $addEv(4,'reasoning_judgement','supported','forecasting','Forecast quarterly demand within 6% error','Forecast vs actual chart (synthetic).');
        $addEv(4,'delivery_reliability','stated','sql','Pulled ledger extracts with SQL for reconciliation');
        // c05 priya — Mechanical (applies to Apex).
        $addEv(5,'initiative_ownership','supported','process_mapping','Mapped and reduced a line changeover by 18%','Process map before/after (synthetic).');
        $addEv(5,'delivery_reliability','stated','python','Scripted sensor data cleaning in Python');
        // c07 mei — Supply chain.
        $addEv(7,'delivery_reliability','supported','excel','Rebuilt the reorder-point model for 200 SKUs','Reorder model workbook (synthetic).');
        $addEv(7,'reasoning_judgement','stated','forecasting','Adjusted safety stock using demand seasonality');
        // c08 farid — Marketing.
        $addEv(8,'collaboration_communication','supported','stakeholder_comm','Turned campaign data into a one-page exec brief','Exec one-pager (synthetic sample).');
        $addEv(8,'delivery_reliability','stated','dashboarding','Built a channel-performance dashboard');
        // c09 nadia — intentionally NO evidence (empty-state demo).

        // ---- Availability pulses (applicants) ----
        foreach ([1,2,3,4,7,8] as $cand) {
            $db->table('availability_pulses')->insert([
                'candidate_id'=>$cand,'state'=>'actively_available','checked_at'=>$now,
                'valid_until'=>date('Y-m-d H:i:s', strtotime('+14 days')),'note'=>null,
            ]);
        }

        // ---- Guided-survey reflections (drive the Meridian reflection layer) ----
        // Map: candidate => [signal => how many of that signal's 3 questions they reflected on].
        $reflect = [
            1 => ['reasoning_judgement'=>3,'delivery_reliability'=>3,'collaboration_communication'=>2,'learning_adaptation'=>2,'initiative_ownership'=>1],
            2 => ['reasoning_judgement'=>2,'delivery_reliability'=>3,'collaboration_communication'=>1,'learning_adaptation'=>2,'initiative_ownership'=>2],
            3 => ['collaboration_communication'=>3,'learning_adaptation'=>3,'reasoning_judgement'=>1,'initiative_ownership'=>1],
            4 => ['reasoning_judgement'=>3,'delivery_reliability'=>2,'learning_adaptation'=>1],
            5 => ['initiative_ownership'=>3,'delivery_reliability'=>2,'reasoning_judgement'=>1],
            7 => ['delivery_reliability'=>3,'reasoning_judgement'=>2,'initiative_ownership'=>1],
            8 => ['collaboration_communication'=>3,'delivery_reliability'=>1],
            // c09_nadia: intentionally no reflections (empty-state demo).
        ];
        $prefix = ['reasoning_judgement'=>'R','delivery_reliability'=>'D','collaboration_communication'=>'C','learning_adaptation'=>'L','initiative_ownership'=>'I'];
        foreach ($reflect as $cand=>$sigs) {
            foreach ($sigs as $sig=>$count) {
                for ($k=1; $k<=$count; $k++) {
                    $db->table('survey_responses')->insert([
                        'candidate_id'=>$cand,'survey_version'=>'edge_v2_15q','question_key'=>$prefix[$sig].$k,'signal'=>$sig,
                        'reflection_choice'=>'Reflected (synthetic)','short_example'=>null,'has_experience'=>1,
                        'visibility'=>'candidate_private','answered_at'=>$now,
                    ]);
                }
            }
        }

        // ---- Roles across three employers ----
        // role 1 / rv1 — Nova (t1) Data Analyst  (amina's demo role — unchanged shape)
        $this->role($db,$now,1,1,1,'data-analyst','Data Analyst',
            'Analyse product usage and build reporting that non-technical teams can act on.','e01_nova',[
            [$skillId['sql'],'SQL','critical','A source-linked query/dashboard you built','delivery_reliability','Which dataset did you query and what decision did it inform?'],
            [$skillId['dashboarding'],'Dashboarding','important','A dashboard artefact or screenshot','delivery_reliability','Who used the dashboard and what changed?'],
            [$skillId['stakeholder_comm'],'Stakeholder Communication','supporting','Explaining analysis to a non-technical audience','collaboration_communication',null],
        ]);
        // role 2 / rv2 — Nova (t1) Business Intelligence Analyst
        $this->role($db,$now,2,1,1,'bi-analyst','Business Intelligence Analyst',
            'Own reporting pipelines and self-serve dashboards for commercial teams.','e01_nova',[
            [$skillId['dashboarding'],'Dashboarding','critical','A production dashboard you own','delivery_reliability','How many people rely on it weekly?'],
            [$skillId['sql'],'SQL','important','A non-trivial query you wrote','delivery_reliability','What did it compute?'],
            [$skillId['data_modelling'],'Data Modelling','supporting','A schema or model you designed','reasoning_judgement',null],
        ]);
        // role 3 / rv3 — Apex (t2) Manufacturing Data Engineer
        $this->role($db,$now,3,2,2,'mfg-data-engineer','Manufacturing Data Engineer',
            'Instrument production lines and turn sensor data into operational decisions.','e02_apex',[
            [$skillId['python'],'Python','critical','A data pipeline or script you built','delivery_reliability','What data did it process?'],
            [$skillId['process_mapping'],'Process Mapping','important','A process you mapped and improved','initiative_ownership','What was the measurable gain?'],
            [$skillId['excel'],'Excel & Spreadsheets','supporting','A model or analysis workbook','delivery_reliability',null],
        ]);
        // role 4 / rv4 — Harbor (t3) Operations Analyst
        $this->role($db,$now,4,3,3,'operations-analyst','Operations Analyst',
            'Improve service operations with forecasting and clear reporting.','e03_harbor',[
            [$skillId['excel'],'Excel & Spreadsheets','critical','A model that drives an operational decision','delivery_reliability','Who acts on it?'],
            [$skillId['forecasting'],'Forecasting','important','A forecast you produced and tracked','reasoning_judgement','How accurate was it?'],
            [$skillId['stakeholder_comm'],'Stakeholder Communication','supporting','Explaining operations data to leadership','collaboration_communication',null],
        ]);

        // ---- Applications across states (the loop, populated) ----
        // #1 amina -> Nova DA, under_review (the primary demo thread) — keep consent id 1.
        $allowed1 = array_map(fn($c)=>$c['id'], $bag[1]);
        $sum1 = $cp->summary($bag[1], $allowed1);
        $db->table('consent_snapshots')->insert(['id'=>1,'candidate_id'=>1,'role_version_id'=>1,
            'allowed_claim_ids'=>json_encode($allowed1),'preview_hash'=>$cp->previewHash($sum1,1),
            'expires_at'=>date('Y-m-d H:i:s', strtotime('+30 days')),'revoked_at'=>null,'created_at'=>$now]);
        $this->app($db,1,1,1,1,1,'under_review','employer','+3 days','Employer started review',[
            ['submitted','c01_amina','Application submitted','-3 days'],
            ['received','e01_nova','Employer received application','-2 days'],
            ['under_review','e01_nova','Employer started review','-1 days'],
        ]);

        // #2 priya -> Apex (stale, no consent) — the operator exception. Keep past expected update.
        $this->app($db,2,5,3,2,null,'under_review','employer','-4 days','Employer started review',[
            ['submitted','c05_priya','Application submitted','-8 days'],
            ['received','e02_apex','Employer received application','-7 days'],
            ['under_review','e02_apex','Employer started review','-6 days'],
        ]);

        // #3 daniel(cand 2) -> Nova DA, received (awaiting review)
        $this->applyWithConsent($db,$cp,$bag,3,2,2,1,1,'received','employer','+2 days','Employer received application',[
            ['submitted','c02_daniel','Application submitted','-1 days'],
            ['received','e01_nova','Employer received application','0 days'],
        ]);
        // #4 siti(cand 3) -> Nova DA, clarification_requested (candidate action)
        $this->applyWithConsent($db,$cp,$bag,4,3,3,1,1,'clarification_requested','candidate','+3 days','Employer requested clarification',[
            ['submitted','c03_siti','Application submitted','-4 days'],
            ['received','e01_nova','Employer received application','-3 days'],
            ['under_review','e01_nova','Employer started review','-2 days'],
            ['clarification_requested','e01_nova','Please add a source for your reporting example','-1 days'],
        ]);
        // #5 farid(cand 8) -> Nova DA, under_review
        $this->applyWithConsent($db,$cp,$bag,5,8,4,1,1,'under_review','employer','+2 days','Employer started review',[
            ['submitted','c08_farid','Application submitted','-2 days'],
            ['received','e01_nova','Employer received application','-1 days'],
            ['under_review','e01_nova','Employer started review','0 days'],
        ]);
        // #6 mei(cand 7) -> Apex MDE, interview_invited (candidate action)
        $this->applyWithConsent($db,$cp,$bag,6,7,5,3,2,'interview_invited','candidate','+5 days','Employer invited to interview',[
            ['submitted','c07_mei','Application submitted','-6 days'],
            ['received','e02_apex','Employer received application','-5 days'],
            ['under_review','e02_apex','Employer started review','-4 days'],
            ['interview_invited','e02_apex','Invited to a first interview','-1 days'],
        ]);
        // #7 aaron(cand 4) -> Harbor Ops, offer_made (candidate action)
        $this->applyWithConsent($db,$cp,$bag,7,4,6,4,3,'offer_made','candidate','+7 days','Employer made an offer',[
            ['submitted','c04_aaron','Application submitted','-12 days'],
            ['under_review','e03_harbor','Employer started review','-9 days'],
            ['interview_completed','e03_harbor','Interview completed','-4 days'],
            ['offer_made','e03_harbor','Offer extended','-1 days'],
        ]);

        // ---- University cohorts + interventions (aggregate only) ----
        $db->table('cohorts')->insert(['id'=>1,'university_id'=>1,'programme'=>'BSc Data Science','intake'=>'2024','consent_policy'=>'aggregate_only']);
        $db->table('cohorts')->insert(['id'=>2,'university_id'=>1,'programme'=>'BEng Mechanical','intake'=>'2024','consent_policy'=>'aggregate_only']);
        $db->table('intervention_cases')->insert(['id'=>1,'cohort_id'=>1,'signal'=>'Low source-backed SQL evidence across cohort',
            'owner'=>'u01_university','plan'=>'Run a SQL evidence workshop; add a portfolio artefact requirement',
            'outcome_metric'=>'Share of cohort with source-backed SQL claim','status'=>'open','created_at'=>$now]);
        $db->table('intervention_cases')->insert(['id'=>2,'cohort_id'=>1,'signal'=>'Few students link dashboards to a decision',
            'owner'=>'u01_university','plan'=>'Add "who acted on it" prompt to portfolio reviews',
            'outcome_metric'=>'Share with decision-linked dashboard evidence','status'=>'in_progress','created_at'=>$now]);

        $this->generateScaleFixtures($db, $now);
    }

    /** Insert a role + its published version + requirements. */
    private function role($db,$now,int $roleId,int $employerId,int $tenant,string $slug,string $title,string $summary,string $author,array $reqs): void
    {
        $db->table('roles')->insert(['id'=>$roleId,'employer_id'=>$employerId,'employer_tenant_id'=>$tenant,
            'slug'=>$slug,'title'=>$title,'lifecycle_status'=>'published','current_version_id'=>$roleId,'created_at'=>$now]);
        $db->table('role_versions')->insert(['id'=>$roleId,'role_id'=>$roleId,'version'=>1,
            'summary'=>$summary,'author'=>$author,'published_at'=>$now]);
        foreach ($reqs as $j=>$r) {
            $db->table('role_requirements')->insert(['role_version_id'=>$roleId,'taxonomy_skill_id'=>$r[0],
                'requirement_label'=>$r[1],'importance'=>$r[2],'evidence_need'=>$r[3],'signal'=>$r[4],'question'=>$r[5]]);
        }
    }

    /** Insert an application + its event timeline. */
    private function app($db,int $id,int $cand,int $rv,int $tenant,?int $consent,string $state,string $owner,string $due,string $last,array $events): void
    {
        $db->table('applications')->insert(['id'=>$id,'candidate_id'=>$cand,'role_version_id'=>$rv,'employer_tenant_id'=>$tenant,
            'consent_snapshot_id'=>$consent,'state'=>$state,'current_owner'=>$owner,
            'expected_update_at'=>date('Y-m-d H:i:s', strtotime($due)),'last_verified_action'=>$last,
            'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        foreach ($events as $e) {
            $db->table('application_events')->insert(['application_id'=>$id,'type'=>$e[0],'actor'=>$e[1],
                'visible_to'=>'candidate,employer','payload_json'=>json_encode(['note'=>$e[2]]),
                'occurred_at'=>date('Y-m-d H:i:s', strtotime($e[3]))]);
        }
    }

    /** Build a consent snapshot from a candidate's evidence, then insert the application. */
    private function applyWithConsent($db,$cp,array $bag,int $appId,int $cand,int $consentId,int $rv,int $tenant,string $state,string $owner,string $due,string $last,array $events): void
    {
        $claims = $bag[$cand] ?? [];
        $allowed = array_map(fn($c)=>$c['id'], $claims);
        $summary = $cp->summary($claims, $allowed);
        $db->table('consent_snapshots')->insert(['id'=>$consentId,'candidate_id'=>$cand,'role_version_id'=>$rv,
            'allowed_claim_ids'=>json_encode($allowed),'preview_hash'=>$cp->previewHash($summary,$rv),
            'expires_at'=>date('Y-m-d H:i:s', strtotime('+30 days')),'revoked_at'=>null,'created_at'=>date('Y-m-d H:i:s')]);
        $this->app($db,$appId,$cand,$rv,$tenant,$consentId,$state,$owner,$due,$last,$events);
    }

    /** 1,000+ synthetic background candidates for scale/empty-state testing only (08). */
    private function generateScaleFixtures($db, string $now): void
    {
        $batch = [];
        for ($i = 1000; $i < 2050; $i++) {
            $batch[] = ['id'=>$i,'identity_key'=>'bg_' . $i,'display_name'=>'Background Candidate ' . $i,
                'discipline'=>'Synthetic','profile_reference_id'=>null,
                'availability_state'=>'unknown_stale','data_classification'=>'synthetic_fixture','created_at'=>$now];
            if (count($batch) >= 200) { $db->table('candidates')->insertBatch($batch); $batch = []; }
        }
        if ($batch) { $db->table('candidates')->insertBatch($batch); }
    }
}
