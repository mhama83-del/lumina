<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\TaxonomyService;

/**
 * Build the Lumina Graph taxonomy from existing students + employer JD.
 * Run: php spark lumina:build-taxonomy
 */
class BuildTaxonomy extends BaseCommand
{
    protected $group       = 'Lumina';
    protected $name        = 'lumina:build-taxonomy';
    protected $description  = 'Seed the self-growing taxonomy (skills + profile patterns) from existing data.';

    public function run(array $params)
    {
        CLI::write('Building Lumina Graph taxonomy…', 'yellow');
        $r = (new TaxonomyService())->seedFromExisting();
        CLI::write('  Inserted skills:   ' . $r['inserted_skills'], 'green');
        CLI::write('  Inserted patterns: ' . $r['inserted_patterns'], 'green');
        CLI::write('  Graph now: ' . $r['skills'] . ' skills · ' . $r['patterns'] . ' patterns · ' . $r['profiles_learned'] . ' profiles learned', 'white');
        CLI::write('Done.', 'yellow');
    }
}
