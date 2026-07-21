<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Continuum\CorePolicy\Service\DemoIdentityRegistry;
use Continuum\Applications\Domain\ConsentPreview;

/**
 * Idempotent demo seeder (08 fixture procedure, 11 demo scenarios).
 * All rows carry data_classification = synthetic_fixture. The 15 named scenarios are curated;
 * background scale rows (1,000+) are generated separately (see generateScaleFixtures()).
 * NEVER labels synthetic data as real Talentbank/employer/market data.
 */
class ContinuumDemoSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $db  = $this->db;

        // Idempotent: clear demo tables first (safe because everything here is synthetic).
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
            if ($p['role']->value !== 'candidate') {
                continue;
            }
            $db->table('candidates')->insert([
                'id'                  => $p['subject'],
                'identity_key'        => $key,
                'display_name'        => $p['name'],
                'discipline'          => $p['discipline'],
                'profile_reference_id'=> 'MOCK-PASSPORT-' . str_pad((string) $p['subject'], 4, '0', STR_PAD_LEFT),
                'availability_state'  => $p['subject'] === 7 ? 'unavailable' : 'actively_available', // c07_mei changes
                'data_classification' => 'synthetic_fixture',
                'created_at'          => $now,
            ]);
        }

        // --- Taxonomy skills ---
        $skills = [
            ['code'=>'sql','label'=>'SQL','domain'=>'data'],
            ['code'=>'python','label'=>'Python','domain'=>'data'],
            ['code'=>'dashboarding','label'=>'Dashboarding','domain'=>'data'],
            ['code'=>'stakeholder_comm','label'=>'Stakeholder Communication','domain'=>'general'],
            ['code'=>'data_modelling','label'=>'Data Modelling','domain'=>'data'],
        ];
        $skillId = [];
        foreach ($skills as $i => $s) {
            $db->table('taxonomy_skills')->insert([
                'id'=>$i+1,'code'=>$s['code'],'label'=>$s['label'],'domain'=>$s['domain'],
            ]);
            $skillId[$s['code']] = $i+1;
        }
        // A related-skill edge: data_modelling ~ SQL. Prompt only — NEVER RER credit (05 §3).
        $db->table('taxonomy_relations')->insert([
            'from_skill'=>$skillId['data_modelling'],'to_skill'=>$skillId['sql'],
            'relation'=>'related_to','confidence'=>0.60,'source'=>'curated_seed','status'=>'approved',
        ]);

        // --- Employer role: Nova Digital (tenant 1) -> "Data Analyst", published v1 ---
        $db->table('roles')->insert([
            'id'=>1,'employer_id'=>1,'employer_tenant_id'=>1,'slug'=>'data-analyst',
            'title'=>'Data Analyst','lifecycle_status'=>'published','current_version_id'=>1,'created_at'=>$now,
        ]);
        $db->table('role_versions')->insert([
            'id'=>1,'role_id'=>1,'version'=>1,
            'summary'=>'Analyse product usage and build reporting that non-technical teams can act on.',
            'author'=>'e01_nova','published_at'=>$now, // published => immutable
        ]);
        $requirements = [
            ['taxonomy_skill_id'=>$skillId['sql'],'requirement_label'=>'SQL','importance'=>'critical',
             'evidence_need'=>'A source-linked query/dashboard you built','signal'=>'delivery_reliability',
             'question'=>'Which dataset did you query and what decision did it inform?'],
            ['taxonomy_skill_id'=>$skillId['dashboarding'],'requirement_label'=>'Dashboarding','importance'=>'important',
             'evidence_need'=>'A dashboard artefact or screenshot','signal'=>'delivery_reliability',
             'question'=>'Who used the dashboard and what did they change because of it?'],
            ['taxonomy_skill_id'=>$skillId['stakeholder_comm'],'requirement_label'=>'Stakeholder Communication','importance'=>'supporting',
             'evidence_need'=>'An example of explaining analysis to a non-technical audience','signal'=>'collaboration_communication',
             'question'=>null],
        ];
        foreach ($requirements as $i => $r) {
            $db->table('role_requirements')->insert(array_merge(['id'=>$i+1,'role_version_id'=>1], $r));
        }

        // --- Evidence for c01_amina (subject 1): strong but ONE Critical SQL source gap (11 demo) ---
        // Dashboarding: Supported (source-linked, approved). SQL: only Stated (needs a source).
        $db->table('evidence_claims')->insert([
            'id'=>1,'candidate_id'=>1,'signal'=>'delivery_reliability','label'=>'supported',
            'claim_text'=>'Built a weekly sales dashboard used by the regional team',
            'confirmation_state'=>'confirmed','taxonomy_skill_id'=>$skillId['dashboarding'],'created_at'=>$now,
        ]);
        $db->table('evidence_sources')->insert([
            'id'=>1,'candidate_id'=>1,'type'=>'link','locator'=>'https://example.org/amina/sales-dashboard',
            'excerpt'=>'Weekly regional sales dashboard (synthetic sample).','added_by'=>'candidate','consentable'=>1,'created_at'=>$now,
        ]);
        $db->table('evidence_links')->insert([
            'id'=>1,'claim_id'=>1,'source_id'=>1,'rationale'=>'Dashboard artefact demonstrates the claim',
            'provenance'=>'candidate_approved','verified_by'=>null,'created_at'=>$now,
        ]);
        // SQL: Stated only (sufficiency 1) — the Critical gap the demo resolves live.
        $db->table('evidence_claims')->insert([
            'id'=>2,'candidate_id'=>1,'signal'=>'delivery_reliability','label'=>'stated',
            'claim_text'=>'Wrote SQL to segment customers for the dashboard',
            'confirmation_state'=>'confirmed','taxonomy_skill_id'=>$skillId['sql'],'created_at'=>$now,
        ]);

        // --- Availability pulse for amina ---
        $db->table('availability_pulses')->insert([
            'id'=>1,'candidate_id'=>1,'state'=>'actively_available','checked_at'=>$now,
            'valid_until'=>date('Y-m-d H:i:s', strtotime('+14 days')),'note'=>null,
        ]);

        // --- Application: amina -> Data Analyst, consent snapshot, under_review (owner employer) ---
        $cp = new ConsentPreview();
        $allowed = [1, 2]; // both claims shared
        $allClaims = [
            ['id'=>1,'signal'=>'delivery_reliability','label'=>'supported','claim_text'=>'Built a weekly sales dashboard used by the regional team','source_excerpt'=>'Weekly regional sales dashboard (synthetic sample).','requirement'=>'Dashboarding'],
            ['id'=>2,'signal'=>'delivery_reliability','label'=>'stated','claim_text'=>'Wrote SQL to segment customers for the dashboard','source_excerpt'=>null,'requirement'=>'SQL'],
        ];
        $summary = $cp->summary($allClaims, $allowed);
        $hash = $cp->previewHash($summary, 1);
        $db->table('consent_snapshots')->insert([
            'id'=>1,'candidate_id'=>1,'role_version_id'=>1,'allowed_claim_ids'=>json_encode($allowed),
            'preview_hash'=>$hash,'expires_at'=>date('Y-m-d H:i:s', strtotime('+30 days')),'revoked_at'=>null,'created_at'=>$now,
        ]);
        $db->table('applications')->insert([
            'id'=>1,'candidate_id'=>1,'role_version_id'=>1,'employer_tenant_id'=>1,'consent_snapshot_id'=>1,
            'state'=>'under_review','current_owner'=>'employer',
            'expected_update_at'=>date('Y-m-d H:i:s', strtotime('+3 days')),
            'last_verified_action'=>'Employer started review','created_at'=>$now,'updated_at'=>$now,
        ]);
        foreach ([
            ['submitted','c01_amina','Application submitted'],
            ['received','e01_nova','Employer received application'],
            ['under_review','e01_nova','Employer started review'],
        ] as $i => $ev) {
            $db->table('application_events')->insert([
                'application_id'=>1,'type'=>$ev[0],'actor'=>$ev[1],'visible_to'=>'candidate,employer',
                'payload_json'=>json_encode(['note'=>$ev[2]]),'occurred_at'=>date('Y-m-d H:i:s', strtotime("-".(3-$i)." days")),
            ]);
        }

        // --- Deliberately STALE application for the operator exception (11 §3:45) ---
        // c05_priya -> Apex (tenant 2). expected_update_at far in the past.
        $db->table('applications')->insert([
            'id'=>2,'candidate_id'=>5,'role_version_id'=>1,'employer_tenant_id'=>2,'consent_snapshot_id'=>null,
            'state'=>'under_review','current_owner'=>'employer',
            'expected_update_at'=>date('Y-m-d H:i:s', strtotime('-4 days')), // overdue beyond grace
            'last_verified_action'=>'Employer started review','created_at'=>$now,'updated_at'=>$now,
        ]);
        $db->table('application_events')->insert([
            'application_id'=>2,'type'=>'under_review','actor'=>'e02_apex','visible_to'=>'candidate,employer',
            'payload_json'=>json_encode(['note'=>'Employer started review']),'occurred_at'=>date('Y-m-d H:i:s', strtotime('-6 days')),
        ]);

        // --- University cohort + one intervention (aggregate only) ---
        $db->table('cohorts')->insert([
            'id'=>1,'university_id'=>1,'programme'=>'BSc Data Science','intake'=>'2024','consent_policy'=>'aggregate_only',
        ]);
        $db->table('intervention_cases')->insert([
            'id'=>1,'cohort_id'=>1,'signal'=>'Low source-backed SQL evidence across cohort',
            'owner'=>'u01_university','plan'=>'Run a SQL evidence workshop; add a portfolio artefact requirement',
            'outcome_metric'=>'Share of cohort with source-backed SQL claim','status'=>'open','created_at'=>$now,
        ]);

        // --- Background scale fixtures (synthetic) ---
        $this->generateScaleFixtures($db, $now);
    }

    /** 1,000+ synthetic background candidates for scale/empty-state testing only (08). */
    private function generateScaleFixtures($db, string $now): void
    {
        $batch = [];
        for ($i = 1000; $i < 2050; $i++) {
            $batch[] = [
                'id'=>$i,'identity_key'=>'bg_' . $i,'display_name'=>'Background Candidate ' . $i,
                'discipline'=>'Synthetic','profile_reference_id'=>null,
                'availability_state'=>'unknown_stale','data_classification'=>'synthetic_fixture','created_at'=>$now,
            ];
            if (count($batch) >= 200) { $db->table('candidates')->insertBatch($batch); $batch = []; }
        }
        if ($batch) { $db->table('candidates')->insertBatch($batch); }
    }
}
