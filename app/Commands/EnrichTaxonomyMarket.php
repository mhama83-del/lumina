<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\TaxonomyService;

/**
 * Grow the Lumina Graph with market-grounded data (Fasa 5):
 * 1) Role-family patterns from the real 1,000-JD dataset (employer_roles +
 *    employer_skill_requirements) — each role family becomes its own clearly
 *    differentiated pattern, keyed "domain|JD: role_family".
 * 2) Curated Malaysia/ASEAN market skills not yet in the graph.
 * Additive + idempotent — never truncates. Safe to re-run any time (e.g. after
 * re-seeding EmployerDatabaseSeeder). Does NOT touch students/resumes.
 * Run: php spark lumina:enrich-taxonomy-market
 */
class EnrichTaxonomyMarket extends BaseCommand
{
    protected $group       = 'Lumina';
    protected $name        = 'lumina:enrich-taxonomy-market';
    protected $description = 'Grow the taxonomy with real JD role-family patterns + curated Malaysia/ASEAN market skills (additive).';

    public function run(array $params)
    {
        CLI::write('Enriching Lumina Graph with market data...', 'yellow');
        $tax = new TaxonomyService();

        $r1 = $tax->seedFromRoles();
        CLI::write("  Role-family patterns: {$r1['role_families']} families -> {$r1['role_patterns_inserted']} new, {$r1['role_patterns_updated']} refreshed", 'green');

        $r2 = $tax->seedMarketSkills();
        CLI::write('  Market skills added: ' . count($r2['added']) . ' (skipped ' . count($r2['skipped']) . ' already known)', 'green');
        if ($r2['added']) CLI::write('    + ' . implode(', ', $r2['added']), 'white');

        $stats = $tax->stats();
        CLI::write("Graph now: {$stats['skills']} skills - {$stats['patterns']} patterns - {$stats['connections']} connections - {$stats['profiles_learned']} profiles learned", 'white');
        CLI::write('Done.', 'yellow');
    }
}
