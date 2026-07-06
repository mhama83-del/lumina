<?php

namespace App\Libraries;

/**
 * WorkAnimal (Fasa 3) — self-discovery onboarding.
 * NOTE: animals/traits below are PLACEHOLDERS. Replace with the real
 * "Your Work Animal" traits from yourworkanimal.com before submission.
 * Talentbank encourages using their Work Animal traits as the core mechanism.
 */
class WorkAnimal
{
    /** Animal definitions: label, trait words, suggested domains. */
    public static function animals(): array
    {
        return [
            'owl'     => ['label' => 'The Owl',     'traits' => ['Analytical', 'Independent', 'Strategic'],   'domains' => ['Data', 'Engineering']],
            'fox'     => ['label' => 'The Fox',     'traits' => ['Adaptable', 'Resourceful', 'Quick'],         'domains' => ['Business', 'Data']],
            'eagle'   => ['label' => 'The Eagle',   'traits' => ['Visionary', 'Decisive', 'Driven'],           'domains' => ['Engineering', 'Business']],
            'dolphin' => ['label' => 'The Dolphin', 'traits' => ['Collaborative', 'Empathetic', 'Communicative'], 'domains' => ['Business']],
            'beaver'  => ['label' => 'The Beaver',  'traits' => ['Methodical', 'Reliable', 'Detailed'],        'domains' => ['Engineering', 'Business']],
            'lion'    => ['label' => 'The Lion',    'traits' => ['Bold', 'Leading', 'Persuasive'],             'domains' => ['Business']],
        ];
    }

    /** 5 tap questions; each option carries weights toward animals. */
    public static function questions(): array
    {
        return [
            ['q' => 'I feel most energised when I…', 'opts' => [
                ['t' => 'Solve a tricky puzzle',        'w' => ['owl' => 2]],
                ['t' => 'Try something new',            'w' => ['fox' => 2]],
                ['t' => 'Lead a team forward',          'w' => ['eagle' => 1, 'lion' => 1]],
                ['t' => 'Help other people',            'w' => ['dolphin' => 2]],
                ['t' => 'Perfect the details',          'w' => ['beaver' => 2]],
            ]],
            ['q' => 'People often describe me as…', 'opts' => [
                ['t' => 'Analytical',  'w' => ['owl' => 2]],
                ['t' => 'Adaptable',   'w' => ['fox' => 2]],
                ['t' => 'Driven',      'w' => ['eagle' => 2]],
                ['t' => 'Caring',      'w' => ['dolphin' => 2]],
                ['t' => 'Reliable',    'w' => ['beaver' => 2]],
            ]],
            ['q' => 'I prefer to work with…', 'opts' => [
                ['t' => 'Data and ideas',       'w' => ['owl' => 2]],
                ['t' => 'Products and markets', 'w' => ['fox' => 2]],
                ['t' => 'A team I lead',        'w' => ['eagle' => 1, 'lion' => 1]],
                ['t' => 'People directly',      'w' => ['dolphin' => 2]],
                ['t' => 'Systems and process',  'w' => ['beaver' => 2]],
            ]],
            ['q' => 'Facing a hard problem, I…', 'opts' => [
                ['t' => 'Analyse it deeply',    'w' => ['owl' => 2]],
                ['t' => 'Improvise a way',      'w' => ['fox' => 2]],
                ['t' => 'Decide fast',          'w' => ['eagle' => 1, 'lion' => 1]],
                ['t' => 'Talk it through',      'w' => ['dolphin' => 2]],
                ['t' => 'Follow a method',      'w' => ['beaver' => 2]],
            ]],
            ['q' => 'The impact I want is…', 'opts' => [
                ['t' => 'Sharp insight',        'w' => ['owl' => 2]],
                ['t' => 'New innovation',       'w' => ['fox' => 1, 'eagle' => 1]],
                ['t' => 'Build something big',  'w' => ['eagle' => 1, 'lion' => 1]],
                ['t' => 'Bring people together','w' => ['dolphin' => 2]],
                ['t' => 'Keep things running',  'w' => ['beaver' => 2]],
            ]],
        ];
    }

    /** Score answers (array of "qIndex:optIndex") -> winning animal id. */
    public static function score(array $answers): string
    {
        $tally = [];
        $qs = self::questions();
        foreach ($answers as $a) {
            [$qi, $oi] = array_pad(explode(':', $a), 2, null);
            $opt = $qs[(int) $qi]['opts'][(int) $oi] ?? null;
            if (! $opt) continue;
            foreach ($opt['w'] as $animal => $w) {
                $tally[$animal] = ($tally[$animal] ?? 0) + $w;
            }
        }
        if (! $tally) return 'owl';
        arsort($tally);
        return array_key_first($tally);
    }

    public static function get(string $id): array
    {
        return self::animals()[$id] ?? self::animals()['owl'];
    }
}
