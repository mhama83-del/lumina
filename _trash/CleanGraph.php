<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
class CleanGraph extends BaseCommand
{
    protected $group='Lumina';
    protected $name='lumina:cleangraph';
    protected $description='Remove job-title / soft-phrase junk skills that leaked into the graph.';
    public function run(array $p)
    {
        $db=\Config\Database::connect();
        $where="label LIKE '% %' AND ("
              ."LOWER(label) LIKE '%coordinator%' OR LOWER(label) LIKE '%management%' OR LOWER(label) LIKE '%manager%' "
              ."OR LOWER(label) LIKE '%executive%' OR LOWER(label) LIKE '%officer%' OR LOWER(label) LIKE '%assistant%' "
              ."OR LOWER(label) LIKE '%supervisor%' OR LOWER(label) LIKE '%leadership%' OR LOWER(label) LIKE '%relationship%' "
              ."OR LOWER(label) LIKE '%mentoring%' OR LOWER(label) LIKE '%coaching%' OR LOWER(label) LIKE '%volunteer%')";
        $rows=$db->query("SELECT code,label,domain FROM taxonomy_skills WHERE $where")->getResultArray();
        CLI::write('  candidates to remove: '.count($rows));
        foreach($rows as $r){ CLI::write('    - '.$r['label'].'  ['.$r['domain'].']'); }
        if ($rows){ $db->query("DELETE FROM taxonomy_skills WHERE $where"); CLI::write('  deleted rows: '.$db->affectedRows()); }
        else { CLI::write('  nothing to remove (already clean)'); }
    }
}
