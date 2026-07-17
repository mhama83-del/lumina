<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
class QaData extends BaseCommand
{
    protected $group='Lumina';
    protected $name='qa:data';
    protected $description='Lumina data sanity counts.';
    public function run(array $params)
    {
        $db=\Config\Database::connect();
        $q=function($sql) use($db){ try { return (int)($db->query($sql)->getRow()->c ?? 0); } catch(\Throwable $e){ return 'n/a'; } };
        $rows=[
          'students total'        => $q("SELECT COUNT(*) c FROM students"),
          'students no-resume'    => $q("SELECT COUNT(*) c FROM students WHERE has_resume=0"),
          'employer_roles total'  => $q("SELECT COUNT(*) c FROM employer_roles"),
          'distinct role domains' => $q("SELECT COUNT(DISTINCT target_domain) c FROM employer_roles"),
          'roles empty domain'    => $q("SELECT COUNT(*) c FROM employer_roles WHERE target_domain IS NULL OR target_domain=''"),
          'taxonomy_skills'       => $q("SELECT COUNT(*) c FROM taxonomy_skills"),
          'taxonomy_edges'        => $q("SELECT COUNT(*) c FROM taxonomy_edges"),
          'taxonomy_patterns'     => $q("SELECT COUNT(*) c FROM taxonomy_patterns"),
        ];
        foreach($rows as $k=>$v){ CLI::write(sprintf("  %-24s : %s",$k,$v)); }
    }
}
