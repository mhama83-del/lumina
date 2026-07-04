<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Lumina Employer/JD database (Fasa 4B.1)
 * Additive only — creates 5 new tables and extends candidate_role_matches.
 * Does NOT touch students, candidate_profiles, resume_analyses, users.
 */
class CreateEmployerTables extends Migration
{
    public function up()
    {
        // ---------- employers ----------
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'company_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'country'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'city'         => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'industry'     => ['type' => 'VARCHAR', 'constraint' => 80],
            'sector'       => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'company_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'company_size' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'is_synthetic' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['country', 'industry']);
        $this->forge->createTable('employers', true);

        // ---------- employer_roles ----------
        $this->forge->addField([
            'id'                        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employer_id'               => ['type' => 'INT', 'unsigned' => true],
            'jd_code'                   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'role_title'                => ['type' => 'VARCHAR', 'constraint' => 160],
            'role_family'               => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'role_level'                => ['type' => 'ENUM', 'constraint' => ['Internship','Fresh Graduate','Graduate Trainee','Junior Executive','Junior Engineer','Junior Analyst'], 'default' => 'Fresh Graduate'],
            'target_domain'             => ['type' => 'ENUM', 'constraint' => ['Data','Engineering','Design','Business'], 'default' => 'Business'],
            'availability_type'         => ['type' => 'ENUM', 'constraint' => ['Internship','Fresh Graduate','Graduate Trainee','Junior Executive','Junior Engineer','Junior Analyst'], 'default' => 'Fresh Graduate'],
            'work_arrangement'          => ['type' => 'ENUM', 'constraint' => ['On-site','Hybrid','Remote'], 'default' => 'On-site'],
            'location_country'          => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'location_city'             => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'region'                    => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'salary_band'               => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'suitable_programmes_json'  => ['type' => 'JSON', 'null' => true],
            'role_summary'              => ['type' => 'TEXT', 'null' => true],
            'responsibilities_json'     => ['type' => 'JSON', 'null' => true],
            'keywords_for_matching_json'=> ['type' => 'JSON', 'null' => true],
            'evidence_required_json'    => ['type' => 'JSON', 'null' => true],
            'learning_velocity_need'    => ['type' => 'ENUM', 'constraint' => ['Low','Medium','High','Extreme'], 'default' => 'Medium'],
            'minimum_cgpa_category'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'match_weighting_json'      => ['type' => 'JSON', 'null' => true],
            'source_reference'          => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'synthetic_jd_text'         => ['type' => 'TEXT', 'null' => true],
            'notes_for_lumina_matching' => ['type' => 'TEXT', 'null' => true],
            'is_synthetic'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'                => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('employer_id');
        $this->forge->addKey('target_domain');
        $this->forge->addKey('role_level');
        $this->forge->addKey('availability_type');
        $this->forge->addKey('location_country');
        $this->forge->addForeignKey('employer_id', 'employers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('employer_roles', true);

        // ---------- employer_skill_requirements ----------
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'role_id'        => ['type' => 'INT', 'unsigned' => true],
            'skill_name'     => ['type' => 'VARCHAR', 'constraint' => 80],
            'skill_code'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'skill_category' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'importance'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'required'],
            'weight'         => ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 1.00],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('role_id');
        $this->forge->addForeignKey('role_id', 'employer_roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('employer_skill_requirements', true);

        // ---------- role_work_animal_fit ----------
        $this->forge->addField([
            'id'                         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'role_id'                    => ['type' => 'INT', 'unsigned' => true],
            'preferred_primary_animal'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'preferred_secondary_animal' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'acceptable_animals_json'    => ['type' => 'JSON', 'null' => true],
            'poor_fit_risk'              => ['type' => 'TEXT', 'null' => true],
            'team_fit_note'              => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('role_id');
        $this->forge->addForeignKey('role_id', 'employer_roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_work_animal_fit', true);

        // ---------- employer_shortlists ----------
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'session_key'      => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'employer_role_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'candidate_ref'    => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('session_key');
        $this->forge->createTable('employer_shortlists', true);

        // ---------- extend candidate_role_matches (guarded) ----------
        $add = [];
        $cols = [
            'employer_role_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'fit_label'               => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'skill_match_score'       => ['type' => 'INT', 'null' => true],
            'evidence_strength_score' => ['type' => 'INT', 'null' => true],
            'learning_velocity_score' => ['type' => 'INT', 'null' => true],
            'animal_fit_score'        => ['type' => 'INT', 'null' => true],
            'domain_fit_score'        => ['type' => 'INT', 'null' => true],
            'academic_fit_score'      => ['type' => 'INT', 'null' => true],
            'skill_overlap_json'      => ['type' => 'JSON', 'null' => true],
            'missing_skills_json'     => ['type' => 'JSON', 'null' => true],
            'explanation'             => ['type' => 'TEXT', 'null' => true],
        ];
        if ($this->db->tableExists('candidate_role_matches')) {
            foreach ($cols as $name => $def) {
                if (! $this->db->fieldExists($name, 'candidate_role_matches')) {
                    $add[$name] = $def;
                }
            }
            if ($add) { $this->forge->addColumn('candidate_role_matches', $add); }
        }
    }

    public function down()
    {
        $this->forge->dropTable('role_work_animal_fit', true);
        $this->forge->dropTable('employer_skill_requirements', true);
        $this->forge->dropTable('employer_shortlists', true);
        $this->forge->dropTable('employer_roles', true);
        $this->forge->dropTable('employers', true);

        $drop = ['employer_role_id','fit_label','skill_match_score','evidence_strength_score',
                 'learning_velocity_score','animal_fit_score','domain_fit_score','academic_fit_score',
                 'skill_overlap_json','missing_skills_json','explanation'];
        if ($this->db->tableExists('candidate_role_matches')) {
            foreach ($drop as $c) {
                if ($this->db->fieldExists($c, 'candidate_role_matches')) {
                    $this->forge->dropColumn('candidate_role_matches', $c);
                }
            }
        }
    }
}
