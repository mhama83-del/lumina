<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Continuum V2 schema (04_DATA_MODEL_AND_CONSENT.md). Each module owns its tables; cross-module
 * access goes through services, never raw cross-table SQL. Provenance / data_classification
 * columns are present where relevant. Indexes on foreign keys, status and due dates (09).
 *
 * Reversible: down() drops in dependency order.
 */
class CreateContinuumV2Schema extends Migration
{
    public function up()
    {
        $f = $this->forge;

        // --- CorePolicy: audit trail (append-only) ---
        $f->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'actor'         => ['type' => 'VARCHAR', 'constraint' => 64],
            'actor_role'    => ['type' => 'VARCHAR', 'constraint' => 32],
            'action'        => ['type' => 'VARCHAR', 'constraint' => 64],
            'resource_type' => ['type' => 'VARCHAR', 'constraint' => 64],
            'resource_id'   => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'outcome'       => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'ok'],
            'meta_json'     => ['type' => 'TEXT', 'null' => true],
            'occurred_at'   => ['type' => 'DATETIME'],
        ]);
        $f->addKey('id', true);
        $f->addKey(['resource_type', 'resource_id']);
        $f->addKey('occurred_at');
        $f->createTable('audit_events', true);

        // --- Evidence: candidates ---
        $f->addField([
            'id'                  => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'identity_key'        => ['type' => 'VARCHAR', 'constraint' => 32],
            'display_name'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'discipline'          => ['type' => 'VARCHAR', 'constraint' => 120],
            'profile_reference_id'=> ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'availability_state'  => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'unknown_stale'],
            'data_classification' => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'synthetic_fixture'],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addUniqueKey('identity_key');
        $f->createTable('candidates', true);

        // --- Evidence: survey responses (candidate_private) ---
        $f->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'candidate_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'survey_version'=> ['type' => 'VARCHAR', 'constraint' => 32],
            'question_key'  => ['type' => 'VARCHAR', 'constraint' => 8],
            'signal'        => ['type' => 'VARCHAR', 'constraint' => 40],
            'reflection_choice' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'short_example' => ['type' => 'TEXT', 'null' => true],
            'has_experience'=> ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'visibility'    => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'candidate_private'],
            'answered_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey(['candidate_id', 'question_key']);
        $f->createTable('survey_responses', true);

        // --- Evidence: claims / sources / links ---
        $f->addField([
            'id'                => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'candidate_id'      => ['type' => 'BIGINT', 'unsigned' => true],
            'signal'            => ['type' => 'VARCHAR', 'constraint' => 40],
            'label'             => ['type' => 'VARCHAR', 'constraint' => 24], // evidence label enum value
            'claim_text'        => ['type' => 'VARCHAR', 'constraint' => 500],
            'confirmation_state'=> ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'unconfirmed'],
            'taxonomy_skill_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey('candidate_id');
        $f->addKey('label');
        $f->createTable('evidence_claims', true);

        $f->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'candidate_id'=> ['type' => 'BIGINT', 'unsigned' => true],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 40],
            'locator'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'excerpt'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'added_by'    => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'candidate'],
            'consentable' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey('candidate_id');
        $f->createTable('evidence_sources', true);

        $f->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'claim_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'source_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'rationale'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'provenance' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'candidate_approved'],
            'verified_by'=> ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey(['claim_id', 'source_id']);
        $f->createTable('evidence_links', true);

        // --- Taxonomy: skills + relations (curated staging/approved) ---
        $f->addField([
            'id'      => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'code'    => ['type' => 'VARCHAR', 'constraint' => 64],
            'label'   => ['type' => 'VARCHAR', 'constraint' => 120],
            'aliases' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'domain'  => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'source'  => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'curated_seed'],
            'version' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'v1'],
            'status'  => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'approved'], // staging|approved
        ]);
        $f->addKey('id', true);
        $f->addUniqueKey('code');
        $f->createTable('taxonomy_skills', true);

        $f->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'from_skill' => ['type' => 'BIGINT', 'unsigned' => true],
            'to_skill'   => ['type' => 'BIGINT', 'unsigned' => true],
            'relation'   => ['type' => 'VARCHAR', 'constraint' => 40],
            'confidence' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 0.50],
            'source'     => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'curated_seed'],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'approved'],
        ]);
        $f->addKey('id', true);
        $f->addKey(['from_skill', 'to_skill']);
        $f->createTable('taxonomy_relations', true);

        // --- Roles: role / role_version / requirement ---
        $f->addField([
            'id'                => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'employer_id'       => ['type' => 'BIGINT', 'unsigned' => true],
            'employer_tenant_id'=> ['type' => 'BIGINT', 'unsigned' => true],
            'slug'              => ['type' => 'VARCHAR', 'constraint' => 80],
            'title'             => ['type' => 'VARCHAR', 'constraint' => 160],
            'lifecycle_status'  => ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'draft'],
            'current_version_id'=> ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addUniqueKey('slug');
        $f->addKey('employer_tenant_id');
        $f->createTable('roles', true);

        $f->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'role_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'version'     => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'summary'     => ['type' => 'TEXT', 'null' => true],
            'author'      => ['type' => 'VARCHAR', 'constraint' => 64],
            'published_at'=> ['type' => 'DATETIME', 'null' => true], // immutable once set
        ]);
        $f->addKey('id', true);
        $f->addKey(['role_id', 'version']);
        $f->createTable('role_versions', true);

        $f->addField([
            'id'               => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'role_version_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'taxonomy_skill_id'=> ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'requirement_label'=> ['type' => 'VARCHAR', 'constraint' => 120],
            'importance'       => ['type' => 'VARCHAR', 'constraint' => 16], // critical|important|supporting
            'evidence_need'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'signal'           => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'question'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey('role_version_id');
        $f->createTable('role_requirements', true);

        // --- Applications: consent / availability / application / events ---
        $f->addField([
            'id'               => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'candidate_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'role_version_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'allowed_claim_ids'=> ['type' => 'VARCHAR', 'constraint' => 500], // JSON array
            'preview_hash'     => ['type' => 'CHAR', 'constraint' => 64],
            'expires_at'       => ['type' => 'DATETIME', 'null' => true],
            'revoked_at'       => ['type' => 'DATETIME', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey('candidate_id');
        $f->createTable('consent_snapshots', true);

        $f->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'candidate_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'state'        => ['type' => 'VARCHAR', 'constraint' => 32],
            'checked_at'   => ['type' => 'DATETIME'],
            'valid_until'  => ['type' => 'DATETIME', 'null' => true],
            'note'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey('candidate_id');
        $f->createTable('availability_pulses', true);

        $f->addField([
            'id'                => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'candidate_id'      => ['type' => 'BIGINT', 'unsigned' => true],
            'role_version_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'employer_tenant_id'=> ['type' => 'BIGINT', 'unsigned' => true],
            'consent_snapshot_id'=> ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'state'             => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'draft'],
            'current_owner'     => ['type' => 'VARCHAR', 'constraint' => 24, 'null' => true],
            'expected_update_at'=> ['type' => 'DATETIME', 'null' => true],
            'last_verified_action' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey(['candidate_id']);
        $f->addKey(['employer_tenant_id', 'state']);
        $f->addKey('expected_update_at'); // stale sweep
        $f->createTable('applications', true);

        $f->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'application_id'=> ['type' => 'BIGINT', 'unsigned' => true],
            'type'          => ['type' => 'VARCHAR', 'constraint' => 48],
            'actor'         => ['type' => 'VARCHAR', 'constraint' => 64],
            'visible_to'    => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'candidate,employer'],
            'payload_json'  => ['type' => 'TEXT', 'null' => true],
            'occurred_at'   => ['type' => 'DATETIME'],
        ]);
        $f->addKey('id', true);
        $f->addKey(['application_id', 'occurred_at']);
        $f->createTable('application_events', true);

        // --- EmployerReview: human review + feedback ---
        $f->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'application_id'=> ['type' => 'BIGINT', 'unsigned' => true],
            'reviewer'      => ['type' => 'VARCHAR', 'constraint' => 64],
            'criterion'     => ['type' => 'VARCHAR', 'constraint' => 120],
            'score_0_to_3'  => ['type' => 'TINYINT', 'null' => true],
            'reason'        => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey('application_id');
        $f->createTable('human_reviews', true);

        $f->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'application_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'category'        => ['type' => 'VARCHAR', 'constraint' => 64],
            'structured_reason'=> ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'visibility'      => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'candidate'],
            'released_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey('application_id');
        $f->createTable('feedback_records', true);

        // --- University: cohort + intervention (aggregate) ---
        $f->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'university_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'programme'     => ['type' => 'VARCHAR', 'constraint' => 120],
            'intake'        => ['type' => 'VARCHAR', 'constraint' => 32],
            'consent_policy'=> ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'aggregate_only'],
        ]);
        $f->addKey('id', true);
        $f->createTable('cohorts', true);

        $f->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'cohort_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'signal'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'owner'         => ['type' => 'VARCHAR', 'constraint' => 64],
            'plan'          => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'outcome_metric'=> ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'open'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey('cohort_id');
        $f->createTable('intervention_cases', true);

        // --- Mentoring (P1 minimal) ---
        $f->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'candidate_id'=> ['type' => 'BIGINT', 'unsigned' => true],
            'topic'       => ['type' => 'VARCHAR', 'constraint' => 160],
            'evidence_gap'=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'availability'=> ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'state'       => ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'requested'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addKey('id', true);
        $f->addKey('candidate_id');
        $f->createTable('mentor_requests', true);
    }

    public function down()
    {
        foreach ([
            'mentor_requests', 'intervention_cases', 'cohorts',
            'feedback_records', 'human_reviews',
            'application_events', 'applications', 'availability_pulses', 'consent_snapshots',
            'role_requirements', 'role_versions', 'roles',
            'taxonomy_relations', 'taxonomy_skills',
            'evidence_links', 'evidence_sources', 'evidence_claims', 'survey_responses',
            'candidates', 'audit_events',
        ] as $t) {
            $this->forge->dropTable($t, true);
        }
    }
}
