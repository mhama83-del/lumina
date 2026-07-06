<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/** One-off, read-only diagnostic. Safe to delete after use. */
class DiagStudents extends BaseCommand
{
    protected $group       = 'lumina';
    protected $name        = 'lumina:diag-students';
    protected $description = 'One-off diagnostic: student count, domain distribution, sample evidence_text';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $count = $db->table('students')->countAllResults();
        CLI::write("count: {$count}");

        CLI::write('--- distribution by target_domain ---');
        $dist = $db->table('students')->select('target_domain, COUNT(*) as n')->groupBy('target_domain')->get()->getResultArray();
        foreach ($dist as $d) { CLI::write("{$d['target_domain']}: {$d['n']}"); }

        CLI::write('--- distribution by programme (top 20) ---');
        $progs = $db->table('students')->select('programme, COUNT(*) as n')->groupBy('programme')->orderBy('n', 'DESC')->limit(20)->get()->getResultArray();
        foreach ($progs as $p) { CLI::write("{$p['programme']}: {$p['n']}"); }

        CLI::write('--- random sample (10) ---');
        $rows = $db->table('students')
            ->select('id, programme, target_domain, evidence_text')
            ->orderBy('RAND()')
            ->limit(10)
            ->get()->getResultArray();
        foreach ($rows as $r) {
            $sample = mb_substr((string) ($r['evidence_text'] ?? ''), 0, 160);
            CLI::write("[{$r['id']}] {$r['programme']} | {$r['target_domain']} | {$sample}");
        }

        CLI::write('--- distinct evidence_text count vs total (duplication check) ---');
        $distinctN = $db->query("SELECT COUNT(DISTINCT evidence_text) AS n FROM students")->getRowArray()['n'];
        CLI::write("distinct evidence_text: {$distinctN} / {$count}");
    }
}
