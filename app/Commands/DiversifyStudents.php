<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\ResumeParserService;

/**
 * lumina:diversify-students (Item #3)
 *
 * In-place UPDATE of the 1,504 `students` rows: richer, more varied
 * evidence_text (varied sentence structure, tools, achievements, Malaysian
 * context) and domain re-alignment using the SAME field-of-study -> domain
 * logic just deployed for resume analysis (ResumeParserService::extractFieldOfStudy),
 * so the cohort benchmark dataset stays consistent with the candidate-facing
 * matching engine.
 *
 * Never touches employers/employer_roles/candidate_profiles/resume_analyses/users.
 * Idempotent-safe to re-run (regenerates deterministically per student id).
 */
class DiversifyStudents extends BaseCommand
{
    protected $group       = 'lumina';
    protected $name        = 'lumina:diversify-students';
    protected $description = 'Re-generate richer/varied evidence_text + re-align domain for the students cohort table.';

    private const OPENERS = [
        'Final-year {P} student who',
        'Penultimate-year {P} student who',
        '{P} student who',
        'Fresh {P} graduate who',
        'Third-year {P} student who',
        'Second-year {P} student who',
        '{P} undergraduate who',
        'Diploma-in-{P} holder who',
    ];

    /** domain => tool/skill phrase bank */
    private const TOOLS = [
        'Engineering-mech' => ['modelled parts in SolidWorks', 'ran FEA stress simulations', 'built a CAD prototype', 'used CATIA for assembly design', 'worked with Six Sigma process improvement', 'operated CNC machining tools'],
        'Engineering-aero' => ['analysed aerodynamics on a glider model', 'studied aircraft structural loads', 'used MATLAB for flight performance modelling', 'worked on an avionics test rig', 'ran wind-tunnel test data analysis'],
        'Engineering-elec' => ['designed circuits in Altium', 'programmed a PLC control system', 'built an embedded system on Arduino', 'worked with Raspberry Pi automation', 'tested electrical schematics for a campus project'],
        'Engineering-civil' => ['ran structural analysis in ETABS', 'produced AutoCAD site drawings', 'supervised a small construction site visit', 'studied reinforced concrete design', 'conducted a land surveying exercise'],
        'Engineering-chem' => ['modelled a distillation column in Aspen Plus', 'studied a plant process safety case', 'ran a mass-transfer lab experiment', 'analysed a P&ID for a pilot plant', 'worked on a separation process report'],
        'Engineering-sw'   => ['built a full-stack web app', 'deployed a service on AWS with Docker', 'wrote REST APIs in Node.js', 'contributed to an open-source repo', 'built a mobile app in Flutter'],
        'Data'             => ['built a dashboard in Power BI', 'ran a machine-learning model in Python', 'cleaned a dataset with pandas', 'wrote SQL queries for a research project', 'trained a classifier using scikit-learn'],
        'Design'           => ['designed a UI prototype in Figma', 'ran a usability test with 8 participants', 'produced a brand identity in Illustrator', 'storyboarded a short animation', 'built a design system component library'],
        'Business'         => ['prepared a financial model in Excel', 'ran a market research survey', 'pitched a business plan to judges', 'analysed a case study for a competition', 'coordinated a marketing campaign on social media'],
    ];

    /** achievement / activity phrase templates */
    private const ACHIEVEMENTS = [
        'won {AWARD} at {EVENT}',
        'presented a project at {EVENT}',
        'completed industrial training at {COMPANY}',
        'led a team of {N} for a {PROJECT}',
        'mentored {N} junior students in the {CLUB}',
        'secured RM{AMT} sponsorship for a {PROJECT}',
        'volunteered for {CAUSE}',
        'served as {ROLE} of the {CLUB}',
        'was a finalist at {EVENT}',
        'co-authored a report on {TOPIC}',
    ];

    private const EVENTS   = ['MyHackathon', 'IMAGINE Cup Malaysia', 'PECIPTA', 'ASEAN Data Science Explorers', 'MTUN Innovation Challenge', 'a national case competition', 'a university innovation fair', 'a regional robotics competition'];
    private const AWARDS   = ['1st runner-up', 'Best Innovation Award', 'Gold Award', 'Best Team Award', 'Merit Award'];
    private const COMPANIES = ['Petronas', 'Maybank', 'AirAsia', 'Shopee', 'Grab', 'CIMB', 'Tenaga Nasional', 'Gamuda', 'a local SME', 'a manufacturing plant'];
    private const CAUSES   = ['a community coding programme', 'a flood-relief drive', 'a campus sustainability project', 'a rural digital-literacy programme', 'a blood donation campaign'];
    private const CLUBS    = ['Robotics Club', 'Student Council', 'Entrepreneurship Society', 'Engineering Society', 'Data Science Club', 'Design Guild', 'debate club'];
    private const ROLES    = ['treasurer', 'vice-president', 'secretary', 'project director', 'head of logistics'];
    private const TOPICS   = ['renewable energy adoption', 'digital financial inclusion', 'campus sustainability', 'process efficiency', 'user experience research'];
    private const PROJECTS = ['capstone project', 'final-year project', 'community outreach project', 'product prototype', 'research project'];

    public function run(array $params)
    {
        $db  = \Config\Database::connect();
        $fos = new ResumeParserService();

        $rows = $db->table('students')->select('id, programme, target_domain')->get()->getResultArray();
        CLI::write('Loaded ' . count($rows) . ' students.');

        $domainChanged = 0; $updated = 0;
        $batch = [];

        foreach ($rows as $row) {
            $id  = (int) $row['id'];
            $programme = (string) ($row['programme'] ?? '');
            $oldDomain = (string) ($row['target_domain'] ?? 'Business');

            // re-align domain using the same logic now used for resume analysis
            $detected = $programme !== '' ? $fos->extractFieldOfStudy($programme) : ['domain' => null];
            $newDomain = $detected['domain'] ?? $oldDomain;
            if ($newDomain !== $oldDomain) $domainChanged++;

            mt_srand(crc32('lumina-student-' . $id)); // deterministic but varied per row

            $toolCat = $this->toolCategoryFor($programme, $newDomain);
            $text = $this->composeEvidence($programme, $newDomain, $toolCat);

            $batch[] = ['id' => $id, 'target_domain' => $newDomain, 'evidence_text' => $text];
            $updated++;

            if (count($batch) >= 300) {
                $db->table('students')->updateBatch($batch, 'id');
                $batch = [];
            }
        }
        if ($batch) $db->table('students')->updateBatch($batch, 'id');

        CLI::write("Updated {$updated} rows. Domain re-aligned on {$domainChanged} rows.");

        $distinctN = $db->query('SELECT COUNT(DISTINCT evidence_text) AS n FROM students')->getRowArray()['n'];
        CLI::write("distinct evidence_text after: {$distinctN} / {$updated}");

        $dist = $db->table('students')->select('target_domain, COUNT(*) as n')->groupBy('target_domain')->get()->getResultArray();
        CLI::write('--- domain distribution after ---');
        foreach ($dist as $d) { CLI::write("{$d['target_domain']}: {$d['n']}"); }
    }

    private function toolCategoryFor(string $programme, string $domain): string
    {
        $p = strtolower($programme);
        if (str_contains($p, 'chemical') || str_contains($p, 'process')) return 'Engineering-chem';
        if (str_contains($p, 'aerospace') || str_contains($p, 'aeronautical')) return 'Engineering-aero';
        if (str_contains($p, 'electric') || str_contains($p, 'electronic')) return 'Engineering-elec';
        if (str_contains($p, 'civil') || str_contains($p, 'structural')) return 'Engineering-civil';
        if (str_contains($p, 'mechanical') || str_contains($p, 'mechatronic') || str_contains($p, 'robotics')) return 'Engineering-mech';
        if (str_contains($p, 'software') || str_contains($p, 'computer') || str_contains($p, 'network') || str_contains($p, 'cyber') || str_contains($p, 'game')) return 'Engineering-sw';
        if ($domain === 'Data') return 'Data';
        if ($domain === 'Design') return 'Design';
        if ($domain === 'Engineering') return 'Engineering-sw';
        return 'Business';
    }

    private function composeEvidence(string $programme, string $domain, string $toolCat): string
    {
        $P = $programme !== '' ? $programme : ucfirst(strtolower($domain));

        $opener = str_replace('{P}', $P, self::pick(self::OPENERS));
        $tool   = self::pick(self::TOOLS[$toolCat] ?? self::TOOLS['Business']);

        $s1 = "{$opener} {$tool}.";

        $n = mt_rand(2, 4);
        $achA = $this->fillAchievement(self::pick(self::ACHIEVEMENTS), $n);
        $achB = $this->fillAchievement(self::pick(self::ACHIEVEMENTS), $n);
        // avoid picking the exact same achievement template twice in a row
        $tries = 0;
        while ($achB === $achA && $tries < 4) { $achB = $this->fillAchievement(self::pick(self::ACHIEVEMENTS), $n); $tries++; }

        $s2 = ucfirst($achA) . '.';
        $s3 = mt_rand(0, 100) < 70 ? ('Also ' . $achB . '.') : '';

        return trim("{$s1} {$s2} {$s3}");
    }

    private function fillAchievement(string $tpl, int $n): string
    {
        return str_replace(
            ['{AWARD}', '{EVENT}', '{COMPANY}', '{N}', '{PROJECT}', '{CLUB}', '{AMT}', '{CAUSE}', '{ROLE}', '{TOPIC}'],
            [self::pick(self::AWARDS), self::pick(self::EVENTS), self::pick(self::COMPANIES), (string) $n,
             self::pick(self::PROJECTS), self::pick(self::CLUBS), (string) (mt_rand(2, 15) * 100),
             self::pick(self::CAUSES), self::pick(self::ROLES), self::pick(self::TOPICS)],
            $tpl
        );
    }

    private static function pick(array $arr): string
    {
        return $arr[array_rand($arr)];
    }
}
