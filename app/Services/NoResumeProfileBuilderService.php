<?php

namespace App\Services;

use App\Libraries\Catalog;

/**
 * NoResumeProfileBuilderService (Fasa 3)
 * Menjana "starter kit" untuk calon tanpa resume: resume draft, first project,
 * recommended activities. Deterministik & explainable.
 */
class NoResumeProfileBuilderService
{
    /** Susun draf resume ringkas daripada medan borang berpandu. */
    public function resumeDraft(array $f, array $skillCodes, string $domain): string
    {
        $name  = $f['name'] ?? 'You';
        $head  = trim(($f['programme'] ?? '') . ($f['stage'] ? '  ·  Stage ' . $f['stage'] : ''));
        if (! empty($f['cgpa']) && is_numeric($f['cgpa'])) $head .= '  ·  CGPA ' . $f['cgpa'];

        $skillLabels = array_map(fn ($c) => Catalog::label($c), $skillCodes);

        $lines   = [];
        $lines[] = $name;
        if ($head) $lines[] = $head;
        $lines[] = '';
        $lines[] = 'SUMMARY';
        $summary = 'Aspiring ' . $domain . ' talent';
        if (! empty($f['leadership'])) $summary .= ', with leadership experience as ' . $f['leadership'];
        if (! empty($f['interest']))   $summary .= '. Focused on ' . $f['interest'];
        $lines[] = $summary . '.';
        $lines[] = '';

        if ($skillLabels) {
            $lines[] = 'SKILLS';
            $lines[] = implode(', ', $skillLabels);
            $lines[] = '';
        }

        $exp = [];
        if (! empty($f['projects']))     $exp[] = '- ' . $f['projects'];
        if (! empty($f['competitions'])) $exp[] = '- Competed in ' . $f['competitions'];
        if (($f['internship'] ?? 'none') === 'completed') $exp[] = '- Completed an internship / industrial training';
        elseif (($f['internship'] ?? 'none') === 'ongoing') $exp[] = '- Currently doing an internship';
        if ($exp) { $lines[] = 'EXPERIENCE & PROJECTS'; array_push($lines, ...$exp); $lines[] = ''; }

        $act = [];
        if (! empty($f['leadership'])) $act[] = '- ' . $f['leadership'] . (! empty($f['activities']) ? ' — ' . $f['activities'] : '');
        elseif (! empty($f['activities'])) $act[] = '- ' . $f['activities'];
        if ($act) { $lines[] = 'LEADERSHIP & ACTIVITIES'; array_push($lines, ...$act); }

        return trim(implode("\n", $lines));
    }

    /** Cadangan projek portfolio pertama mengikut domain. */
    public function firstProject(string $domain, array $gapCodes): string
    {
        $tool = $gapCodes[0] ?? null;
        $tl   = $tool ? Catalog::label($tool) : null;
        return [
            'Data'        => 'Build a 1-page dashboard from a public Malaysian open dataset (data.gov.my)' . ($tl ? ' using ' . $tl : '') . ' — surface 3 clear insights.',
            'Engineering' => 'Build a small CRUD web app (e.g. a task tracker) with a REST API and deploy it free (Render / Vercel).',
            'Design'      => 'Redesign one screen of a Malaysian app in Figma — deliver a clickable prototype plus a short rationale.',
            'Business'    => 'Run a mini market analysis of a product you like — a 1-page recommendation backed by simple data.',
        ][$domain] ?? 'Ship one small, finishable project this month and write a paragraph on what you learned.';
    }

    /** Aktiviti disyorkan mengikut domain. */
    public function activities(string $domain): array
    {
        return [
            'Data'        => ['Join a data / analytics club or a Kaggle group', 'Enter a campus data competition', 'Volunteer to build a dashboard for a society'],
            'Engineering' => ['Join a coding club or a hackathon', 'Contribute to a small open-source repo', 'Build and ship one side project'],
            'Design'      => ['Join a design club', 'Do a 100-day UI challenge', 'Volunteer poster / branding for an event'],
            'Business'    => ['Join Enactus or a business club', 'Enter a case competition', 'Run a small campaign for a society'],
        ][$domain] ?? ['Join a club aligned to your interest', 'Enter one competition this semester', 'Volunteer for a real project'];
    }
}
