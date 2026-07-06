<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\EmployerRoleModel;
use App\Services\TalentMatchService;

/**
 * Fasa 4B.6 — matching validation across 20 roles (Gemini Bahagian 13/14).
 * Run: php spark lumina:test-matching
 */
class TestMatching extends BaseCommand
{
    protected $group       = 'Lumina';
    protected $name        = 'lumina:test-matching';
    protected $description  = 'Rank the student cohort against 20 representative roles and report fit distribution.';

    private array $families = [
        'Data Analyst','SOC Analyst','Product Analyst','Health Data Analyst','ESG Data Analyst',
        'Aerospace Engineer','Site Engineer','Semiconductor Equipment Engineer','Renewable Energy Engineer','Network Support',
        'Management Trainee','Audit Associate','Tax Associate','Sustainability ESG Associate','Supply Chain Executive','Syariah Compliance Associate',
        'UX Designer','Product Designer','Content Creator','Motion Designer',
    ];

    public function run(array $params)
    {
        $db  = db_connect();
        $rm  = new EmployerRoleModel();
        $svc = new TalentMatchService();

        // stated skills
        $statedMap = [];
        try {
            foreach ($db->table('student_skills ss')->select('ss.student_id, sk.code')
                ->join('skills sk', 'sk.id = ss.skill_id')->get()->getResultArray() as $r) {
                $statedMap[$r['student_id']][] = $r['code'];
            }
        } catch (\Throwable $e) {}

        $students = $db->table('students')
            ->select('id, name, university, programme, target_domain, evidence_text, has_resume, cgpa')
            ->limit(500)->get()->getResultArray();
        $n = count($students);

        // pre-build signals once (reused across roles)
        $signals = [];
        foreach ($students as $s) { $signals[$s['id']] = TalentMatchService::buildStudentSignal($s, $statedMap[$s['id']] ?? []); }

        CLI::write('==================================================', 'yellow');
        CLI::write(' LUMINA MATCHING TEST — 20 ROLES (cohort sample: ' . $n . ')', 'yellow');
        CLI::write('==================================================', 'yellow');

        $rolesWithGood = 0;
        foreach ($this->families as $fam) {
            $roleRow = $db->table('employer_roles')->where('role_family', $fam)->orderBy('id', 'ASC')->get(1)->getRowArray();
            if (! $roleRow) { CLI::write("\n[skip] no role for {$fam}", 'red'); continue; }
            $role = $rm->fullRole((int) $roleRow['id']);

            $scored = [];
            foreach ($students as $s) {
                $m = $svc->match($signals[$s['id']], $role);
                $scored[] = ['name' => $s['name'], 'score' => $m['match_score'], 'label' => $m['fit_label']];
            }
            usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

            $buckets = ['Strong Match' => 0, 'Good Match' => 0, 'Potential Match' => 0, 'Needs Development' => 0, 'Weak Match' => 0];
            foreach ($scored as $x) { $buckets[$x['label']]++; }
            $top = $scored[0];
            if ($buckets['Strong Match'] + $buckets['Good Match'] > 0) $rolesWithGood++;

            $col = $top['score'] >= 70 ? 'green' : ($top['score'] >= 55 ? 'yellow' : 'light_gray');
            CLI::write("\n" . str_pad($fam, 34) . '  ' . CLI::color("top {$top['score']}% ({$top['name']})", $col), 'white');
            CLI::write('   ' . $role['company_name'] . ' [' . $role['target_domain'] . '/' . $role['role_level'] . ']  '
                . "Strong {$buckets['Strong Match']} · Good {$buckets['Good Match']} · Potential {$buckets['Potential Match']} · NeedsDev {$buckets['Needs Development']} · Weak {$buckets['Weak Match']}");
        }

        CLI::write("\n--------------------------------------------------", 'yellow');
        CLI::write(' Roles with >=1 Good/Strong match: ' . $rolesWithGood . ' / ' . count($this->families), 'green');
        CLI::write(' (Healthy = most roles surface at least a few strong-fit candidates.)');
        CLI::write('Done.', 'yellow');
    }
}
