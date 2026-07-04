<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Libraries\LuminaJdGenerator;

/**
 * EmployerDatabaseSeeder (Fasa 4B.3)
 * Seeds ~1,000 synthetic employer JDs from LuminaJdGenerator.
 * Idempotent: removes ONLY synthetic employer data (is_synthetic=1) then re-seeds.
 * Never touches students, candidate_profiles, resume_analyses, users.
 *
 * Run: php spark db:seed EmployerDatabaseSeeder
 */
class EmployerDatabaseSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $records = LuminaJdGenerator::generate();
        echo 'Generated ' . count($records) . " JD records.\n";

        // ---- idempotent cleanup (FK cascade removes roles/skills/animal_fit) ----
        $this->db->table('employers')->where('is_synthetic', 1)->delete();
        echo "Cleared previous synthetic employers (cascade).\n";

        // ---- 1) employers (dedupe by name|country) ----
        $empSeen = [];
        foreach ($records as $r) {
            $e   = $r['employer'];
            $key = $e['company_name'] . '|' . $e['country'];
            if (! isset($empSeen[$key])) {
                $empSeen[$key] = $e + ['is_synthetic' => 1, 'created_at' => $now];
            }
        }
        foreach (array_chunk(array_values($empSeen), 100) as $chunk) {
            $this->db->table('employers')->insertBatch($chunk);
        }
        echo 'Inserted ' . count($empSeen) . " employers.\n";

        $map = [];
        foreach ($this->db->table('employers')->select('id, company_name, country')
                 ->where('is_synthetic', 1)->get()->getResultArray() as $row) {
            $map[$row['company_name'] . '|' . $row['country']] = (int) $row['id'];
        }

        // ---- 2) roles (one-by-one for exact id) + collect skills/animal ----
        $skillRows = []; $animalRows = [];
        $roleTotal = 0; $skillTotal = 0; $animalTotal = 0;

        $flushSkills = function () use (&$skillRows, &$skillTotal) {
            if (! $skillRows) return;
            $this->db->table('employer_skill_requirements')->insertBatch($skillRows);
            $skillTotal += count($skillRows); $skillRows = [];
        };
        $flushAnimal = function () use (&$animalRows, &$animalTotal) {
            if (! $animalRows) return;
            $this->db->table('role_work_animal_fit')->insertBatch($animalRows);
            $animalTotal += count($animalRows); $animalRows = [];
        };

        foreach ($records as $r) {
            $e = $r['employer']; $role = $r['role'];
            $empId = $map[$e['company_name'] . '|' . $e['country']] ?? null;
            if (! $empId) continue;

            $this->db->table('employer_roles')->insert([
                'employer_id' => $empId,
                'jd_code' => $role['jd_code'], 'role_title' => $role['role_title'], 'role_family' => $role['role_family'],
                'role_level' => $role['role_level'], 'target_domain' => $role['target_domain'],
                'availability_type' => $role['availability_type'], 'work_arrangement' => $role['work_arrangement'],
                'location_country' => $role['location_country'], 'location_city' => $role['location_city'],
                'region' => $role['region'], 'salary_band' => $role['salary_band'],
                'suitable_programmes_json' => json_encode($role['suitable_programmes']),
                'role_summary' => $role['role_summary'],
                'responsibilities_json' => json_encode($role['responsibilities']),
                'keywords_for_matching_json' => json_encode($role['keywords']),
                'evidence_required_json' => json_encode($role['evidence_required']),
                'learning_velocity_need' => $role['learning_velocity_need'],
                'minimum_cgpa_category' => $role['minimum_cgpa_category'],
                'match_weighting_json' => json_encode($role['match_weighting']),
                'source_reference' => $role['source_reference'],
                'synthetic_jd_text' => $role['synthetic_jd_text'],
                'notes_for_lumina_matching' => $role['notes_for_lumina_matching'],
                'is_synthetic' => 1, 'created_at' => $now,
            ]);
            $roleId = (int) $this->db->insertID();
            $roleTotal++;

            foreach ($r['skills'] as $s) {
                $skillRows[] = [
                    'role_id' => $roleId, 'skill_name' => $s['skill_name'], 'skill_code' => $s['skill_code'],
                    'skill_category' => $s['skill_category'], 'importance' => $s['importance'], 'weight' => $s['weight'],
                ];
            }
            $a = $r['animal'];
            $animalRows[] = [
                'role_id' => $roleId,
                'preferred_primary_animal' => $a['preferred_primary_animal'],
                'preferred_secondary_animal' => $a['preferred_secondary_animal'],
                'acceptable_animals_json' => json_encode($a['acceptable']),
                'poor_fit_risk' => $a['poor_fit_risk'], 'team_fit_note' => $a['team_fit_note'],
            ];

            if (count($skillRows) >= 500) { $flushSkills(); }
            if (count($animalRows) >= 500) { $flushAnimal(); }
        }
        $flushSkills(); $flushAnimal();

        echo "Inserted {$roleTotal} roles, {$skillTotal} skill requirements, {$animalTotal} animal-fit rows.\n";

        $dist = $this->db->table('employer_roles')->select('target_domain, COUNT(*) c')
            ->where('is_synthetic', 1)->groupBy('target_domain')->orderBy('c', 'DESC')->get()->getResultArray();
        echo "Domain distribution:\n";
        foreach ($dist as $d) { echo "  {$d['target_domain']}: {$d['c']}\n"; }
        echo "Done.\n";
    }
}
