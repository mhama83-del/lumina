<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
class DiagGraph extends BaseCommand
{
    protected $group = 'lumina';
    protected $name = 'lumina:diag-graph';
    protected $description = 'Diagnose why /graph is not showing all 11 domains';
    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('--- employer_roles domain distribution (is_synthetic=1) ---');
        foreach ($db->table('employer_roles')->select('target_domain, COUNT(*) as n')->where('is_synthetic', 1)->groupBy('target_domain')->orderBy('n','DESC')->get()->getResultArray() as $r) {
            CLI::write("{$r['target_domain']}: {$r['n']}");
        }

        CLI::write('--- taxonomy_patterns domain distribution (JD: prefix only) ---');
        foreach ($db->table('taxonomy_patterns')->select('domain, COUNT(*) as n')->like('programme', 'JD:%', 'after')->groupBy('domain')->orderBy('n','DESC')->get()->getResultArray() as $r) {
            CLI::write("{$r['domain']}: {$r['n']}");
        }

        CLI::write('--- taxonomy_skills domain distribution ---');
        foreach ($db->table('taxonomy_skills')->select('domain, COUNT(*) as n')->groupBy('domain')->orderBy('n','DESC')->get()->getResultArray() as $r) {
            CLI::write("{$r['domain']}: {$r['n']}");
        }

        CLI::write('--- total employer_roles rows (synthetic) ---');
        CLI::write((string) $db->table('employer_roles')->where('is_synthetic', 1)->countAllResults());
    }
}
