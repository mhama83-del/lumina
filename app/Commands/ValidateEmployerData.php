<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Fasa 4B.4 — Employer/JD data quality control.
 * Run: php spark lumina:validate-employer-data
 */
class ValidateEmployerData extends BaseCommand
{
    protected $group       = 'Lumina';
    protected $name        = 'lumina:validate-employer-data';
    protected $description = 'Validate the 1,000-JD employer dataset (distribution + QC rules).';

    public function run(array $params)
    {
        $db = db_connect();
        $one = fn ($sql) => (int) ($db->query($sql)->getRowArray()['n'] ?? 0);

        CLI::write('==================================================', 'yellow');
        CLI::write(' LUMINA EMPLOYER DATA — QC REPORT', 'yellow');
        CLI::write('==================================================', 'yellow');

        // ---- totals ----
        $emp   = $one('SELECT COUNT(*) n FROM employers WHERE is_synthetic=1');
        $roles = $one('SELECT COUNT(*) n FROM employer_roles WHERE is_synthetic=1');
        $skills= $one('SELECT COUNT(*) n FROM employer_skill_requirements');
        $anim  = $one('SELECT COUNT(*) n FROM role_work_animal_fit');
        CLI::write("\nTOTALS", 'green');
        CLI::write("  employers: {$emp}");
        CLI::write("  roles (JD): {$roles}");
        CLI::write("  skill requirements: {$skills}");
        CLI::write("  animal-fit rows: {$anim}");

        $this->dist($db, 'target_domain', 'Domain');
        $this->dist($db, 'role_level', 'Role level');
        $this->dist($db, 'location_country', 'Country');
        $this->distJoin($db, 'e.company_type', 'Company type');
        $this->families($db);

        // ---- QC rules ----
        CLI::write("\nQC RULES", 'green');
        $target = ['Business' => 320, 'Engineering' => 300, 'Data' => 250, 'Design' => 130];
        $domOk = true;
        foreach ($target as $d => $t) {
            $c = $one("SELECT COUNT(*) n FROM employer_roles WHERE target_domain='{$d}'");
            if (abs($c - $t) > 30) $domOk = false;
        }

        $this->rule('R1  Total roles = 1000', $roles === 1000);
        $this->rule('R2  Domain distribution within tolerance', $domOk);
        $this->rule('R3  Every role has an employer', $one('SELECT COUNT(*) n FROM employer_roles r LEFT JOIN employers e ON e.id=r.employer_id WHERE e.id IS NULL') === 0);
        $this->rule('R4  Valid target_domain only', $one("SELECT COUNT(*) n FROM employer_roles WHERE target_domain NOT IN ('Data','Engineering','Design','Business')") === 0);
        $this->rule('R5  Every role has required skills', $one('SELECT COUNT(*) n FROM employer_roles r WHERE NOT EXISTS (SELECT 1 FROM employer_skill_requirements s WHERE s.role_id=r.id AND s.importance=\'required\')') === 0);
        $this->rule('R6  Every role has preferred skills', $one('SELECT COUNT(*) n FROM employer_roles r WHERE NOT EXISTS (SELECT 1 FROM employer_skill_requirements s WHERE s.role_id=r.id AND s.importance=\'preferred\')') === 0);
        $this->rule('R7  Every role has soft skills', $one('SELECT COUNT(*) n FROM employer_roles r WHERE NOT EXISTS (SELECT 1 FROM employer_skill_requirements s WHERE s.role_id=r.id AND s.skill_category=\'Soft Skill\')') === 0);
        $this->rule('R8  keywords_for_matching_json present', $one("SELECT COUNT(*) n FROM employer_roles WHERE keywords_for_matching_json IS NULL OR JSON_LENGTH(keywords_for_matching_json)=0") === 0);
        $this->rule('R9  evidence_required_json present', $one("SELECT COUNT(*) n FROM employer_roles WHERE evidence_required_json IS NULL OR JSON_LENGTH(evidence_required_json)=0") === 0);
        $this->rule('R10 suitable_programmes present', $one("SELECT COUNT(*) n FROM employer_roles WHERE suitable_programmes_json IS NULL OR JSON_LENGTH(suitable_programmes_json)=0") === 0);
        $this->rule('R11 Every role has animal fit', $one('SELECT COUNT(*) n FROM employer_roles r WHERE NOT EXISTS (SELECT 1 FROM role_work_animal_fit a WHERE a.role_id=r.id)') === 0);
        $this->rule('R12 Animals use full 12-name set', $one("SELECT COUNT(*) n FROM role_work_animal_fit WHERE preferred_primary_animal NOT IN ('Lion','Eagle','Wolf','Owl','Dolphin','Peacock','Elephant','Horse','Ant','Cheetah','Fox','Octopus')") === 0);
        $this->rule('R13 Salary band present', $one("SELECT COUNT(*) n FROM employer_roles WHERE salary_band IS NULL OR salary_band=''") === 0);
        $this->rule('R14 Internship not demanding years of exp', $one("SELECT COUNT(*) n FROM employer_roles WHERE availability_type='Internship' AND (synthetic_jd_text LIKE '%years of experience%' OR synthetic_jd_text LIKE '%3 years%' OR synthetic_jd_text LIKE '%5 years%')") === 0);
        $this->rule('R15 All JSON valid (keywords)', $one('SELECT COUNT(*) n FROM employer_roles WHERE JSON_VALID(keywords_for_matching_json)=0') === 0);
        $this->rule('R16 source_reference present', $one("SELECT COUNT(*) n FROM employer_roles WHERE source_reference IS NULL OR source_reference=''") === 0);
        $this->rule('R17 Design roles waive CGPA (N/A)', $one("SELECT COUNT(*) n FROM employer_roles WHERE target_domain='Design' AND minimum_cgpa_category<>'N/A'") === 0);
        $this->rule('R18 No duplicate role_title >2 per employer', $one('SELECT COUNT(*) n FROM (SELECT employer_id, role_title, COUNT(*) c FROM employer_roles GROUP BY employer_id, role_title HAVING c>2) x') === 0);

        CLI::write("\nExamples (10 JD):", 'green');
        $rows = $db->query("SELECT r.jd_code, e.company_name, r.role_title, r.target_domain, r.role_level, r.location_country, r.salary_band
                            FROM employer_roles r JOIN employers e ON e.id=r.employer_id ORDER BY r.id LIMIT 10")->getResultArray();
        foreach ($rows as $x) {
            CLI::write("  {$x['jd_code']}  {$x['company_name']} — {$x['role_title']} [{$x['target_domain']}/{$x['role_level']}] {$x['location_country']} · {$x['salary_band']}");
        }
        CLI::write("\nDone.", 'yellow');
    }

    private function dist($db, string $col, string $label): void
    {
        CLI::write("\n{$label} distribution", 'green');
        $rows = $db->query("SELECT {$col} k, COUNT(*) n FROM employer_roles GROUP BY {$col} ORDER BY n DESC")->getResultArray();
        foreach ($rows as $r) { CLI::write('  ' . str_pad($r['k'], 22) . $r['n']); }
    }

    private function distJoin($db, string $col, string $label): void
    {
        CLI::write("\n{$label} distribution", 'green');
        $rows = $db->query("SELECT {$col} k, COUNT(*) n FROM employer_roles r JOIN employers e ON e.id=r.employer_id GROUP BY {$col} ORDER BY n DESC")->getResultArray();
        foreach ($rows as $r) { CLI::write('  ' . str_pad($r['k'], 22) . $r['n']); }
    }

    private function families($db): void
    {
        CLI::write("\nTop 20 role families", 'green');
        $rows = $db->query("SELECT role_family k, COUNT(*) n FROM employer_roles GROUP BY role_family ORDER BY n DESC LIMIT 20")->getResultArray();
        foreach ($rows as $r) { CLI::write('  ' . str_pad($r['k'], 34) . $r['n']); }
    }

    private function rule(string $label, bool $pass): void
    {
        CLI::write('  ' . ($pass ? CLI::color('PASS', 'green') : CLI::color('FAIL', 'red')) . '  ' . $label);
    }
}
