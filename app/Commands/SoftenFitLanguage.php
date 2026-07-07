<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Reframe role_work_animal_fit.poor_fit_risk from blunt/deficit language
 * to supportive, bias-safe "may need support" framing. Deterministic + idempotent.
 * Run: php spark lumina:soften-fit
 */
class SoftenFitLanguage extends BaseCommand
{
    protected $group       = 'Lumina';
    protected $name        = 'lumina:soften-fit';
    protected $description  = 'Rewrite poor-fit wording to supportive, non-biased language.';

    private array $map = [
        'Highly rigid conformists'        => 'More structured, procedure-oriented profiles',
        'Rigid conformists'               => 'More structured profiles',
        'Highly loyal but rigid individuals' => 'Loyal, structure-loving profiles',
        'Impersonal profiles'             => 'More task-focused profiles',
        'Reckless innovators'             => 'Fast-moving innovators',
        'Reckless'                        => 'Fast-moving',
        'Aggressive sprinters'            => 'High-energy sprinters',
        'Overly optimistic profiles'      => 'Optimistic profiles',
        'Overly theoretical individuals'  => 'More theoretical profiles',
        'Highly theoretical individuals'  => 'More theoretical profiles',
        'Pure introverts'                 => 'More reflective profiles',
        'Commanding types'                => 'More directive profiles',
        'fail to navigate'                => 'may need support navigating',
        'will fail to'                    => 'may need support to',
        'fail to'                         => 'may need support to',
        'will suffer from'                => 'may need support with',
        'will suffer'                     => 'may need support',
        'suffer from'                     => 'may need support with',
        'will become highly frustrated with' => 'may need extra engagement with',
        'will be paralyzed by'            => 'may need help with',
        'be paralyzed by'                 => 'may need help with',
        'struggle with'                   => 'may need support with',
        'struggle to'                     => 'may need support to',
        'might lack'                      => 'may need to build',
        'might enforce their own biases instead of listening empathetically to' => 'may need reminders to centre',
        'present a physical danger in'    => 'need extra safety supervision in',
        'lose motivation'                 => 'may need extra engagement',
        'loss of motivation'              => 'reduced engagement',
        'without execution discipline'    => 'still building execution discipline',
        'without documentation discipline'=> 'still building documentation habits',
        'without communication evidence'  => 'still building communication evidence',
        'without safety discipline'       => 'still building safety discipline',
        'without ambiguity tolerance'     => 'still building comfort with ambiguity',
        'without user empathy or creativity evidence' => 'still building user-empathy evidence',
        'without discipline'              => 'still building discipline',
        'without data evidence'           => 'still building data evidence',
    ];

    public function run(array $params)
    {
        $db  = db_connect();
        $rows = $db->table('role_work_animal_fit')->select('id, poor_fit_risk')
            ->where('poor_fit_risk !=', '')->get()->getResultArray();
        $find = array_keys($this->map); $repl = array_values($this->map);
        $changed = 0;
        foreach ($rows as $r) {
            $new = str_ireplace($find, $repl, $r['poor_fit_risk']);
            if ($new !== $r['poor_fit_risk']) {
                $db->table('role_work_animal_fit')->where('id', $r['id'])->update(['poor_fit_risk' => $new]);
                $changed++;
            }
        }
        CLI::write('Softened ' . $changed . ' of ' . count($rows) . ' poor-fit statements.', 'green');
        CLI::write('Sample after:', 'yellow');
        foreach ($db->table('role_work_animal_fit')->select('poor_fit_risk')->where('poor_fit_risk !=', '')->limit(3)->get()->getResultArray() as $x) {
            CLI::write('  ' . mb_substr($x['poor_fit_risk'], 0, 90));
        }
        CLI::write('Done.', 'yellow');
    }
}
