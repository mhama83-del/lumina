<?php

namespace App\Libraries;

/**
 * WorkAnimal (Fasa 6) — self-discovery onboarding, now 12 archetypes.
 * Grouped: Leadership (Lion, Eagle, Wolf, Owl), Relational (Dolphin, Peacock,
 * Elephant, Horse), Execution (Ant, Cheetah, Fox, Octopus).
 * Evidence-based inference lives in App\Services\AnimalInferenceService; this
 * class powers the quick tap-quiz for the No-Resume path.
 */
class WorkAnimal
{
    public static function animals(): array
    {
        return [
            'lion'     => ['label' => 'The Lion',     'traits' => ['Bold', 'Decisive', 'Persuasive'],        'domains' => ['Business']],
            'eagle'    => ['label' => 'The Eagle',    'traits' => ['Visionary', 'Strategic', 'Driven'],       'domains' => ['Business', 'Engineering']],
            'wolf'     => ['label' => 'The Wolf',     'traits' => ['Coordinating', 'Loyal', 'Team-driven'],   'domains' => ['Business', 'Engineering']],
            'owl'      => ['label' => 'The Owl',      'traits' => ['Analytical', 'Independent', 'Precise'],    'domains' => ['Data', 'Engineering']],
            'dolphin'  => ['label' => 'The Dolphin',  'traits' => ['Collaborative', 'Empathetic', 'Communicative'], 'domains' => ['Business', 'Design']],
            'peacock'  => ['label' => 'The Peacock',  'traits' => ['Expressive', 'Persuasive', 'Creative'],   'domains' => ['Business', 'Design']],
            'elephant' => ['label' => 'The Elephant', 'traits' => ['Patient', 'Supportive', 'Wise'],          'domains' => ['Business', 'Design']],
            'horse'    => ['label' => 'The Horse',    'traits' => ['Reliable', 'Steady', 'Dependable'],        'domains' => ['Business', 'Engineering']],
            'ant'      => ['label' => 'The Ant',      'traits' => ['Methodical', 'Reliable', 'Detailed'],      'domains' => ['Engineering', 'Data']],
            'cheetah'  => ['label' => 'The Cheetah',  'traits' => ['Fast', 'Agile', 'Competitive'],            'domains' => ['Data', 'Business']],
            'fox'      => ['label' => 'The Fox',      'traits' => ['Adaptable', 'Resourceful', 'Quick'],       'domains' => ['Business', 'Data']],
            'octopus'  => ['label' => 'The Octopus',  'traits' => ['Versatile', 'Creative', 'Hands-on'],       'domains' => ['Design', 'Engineering']],
        ];
    }

    /** 5 tap questions; each option carries weights toward archetypes. */
    public static function questions(): array
    {
        return [
            ['q' => 'I feel most energised when I…', 'opts' => [
                ['t' => 'Solve a tricky puzzle',        'w' => ['owl' => 2]],
                ['t' => 'Try something new fast',       'w' => ['cheetah' => 1, 'fox' => 1]],
                ['t' => 'Lead a team forward',          'w' => ['lion' => 1, 'wolf' => 1]],
                ['t' => 'Help other people',            'w' => ['dolphin' => 2]],
                ['t' => 'Build something with my hands','w' => ['octopus' => 1, 'ant' => 1]],
            ]],
            ['q' => 'People often describe me as…', 'opts' => [
                ['t' => 'Analytical',  'w' => ['owl' => 2]],
                ['t' => 'Visionary',   'w' => ['eagle' => 2]],
                ['t' => 'Caring',      'w' => ['elephant' => 1, 'dolphin' => 1]],
                ['t' => 'Reliable',    'w' => ['horse' => 1, 'ant' => 1]],
                ['t' => 'Expressive',  'w' => ['peacock' => 2]],
            ]],
            ['q' => 'I prefer to work with…', 'opts' => [
                ['t' => 'Data and ideas',       'w' => ['owl' => 2]],
                ['t' => 'Products and markets', 'w' => ['fox' => 2]],
                ['t' => 'A team I lead',         'w' => ['lion' => 1, 'wolf' => 1]],
                ['t' => 'People directly',       'w' => ['dolphin' => 1, 'peacock' => 1]],
                ['t' => 'Systems and process',   'w' => ['ant' => 2]],
            ]],
            ['q' => 'Facing a hard problem, I…', 'opts' => [
                ['t' => 'Analyse it deeply',    'w' => ['owl' => 2]],
                ['t' => 'Improvise a way',      'w' => ['fox' => 1, 'cheetah' => 1]],
                ['t' => 'Decide fast and lead', 'w' => ['lion' => 2]],
                ['t' => 'Talk it through',      'w' => ['dolphin' => 1, 'elephant' => 1]],
                ['t' => 'Follow a method',      'w' => ['horse' => 1, 'ant' => 1]],
            ]],
            ['q' => 'The impact I want is…', 'opts' => [
                ['t' => 'Sharp insight',         'w' => ['owl' => 2]],
                ['t' => 'A bold new venture',    'w' => ['eagle' => 1, 'cheetah' => 1]],
                ['t' => 'Build something big',   'w' => ['lion' => 1, 'wolf' => 1]],
                ['t' => 'Bring people together', 'w' => ['dolphin' => 1, 'elephant' => 1]],
                ['t' => 'Craft something great', 'w' => ['octopus' => 1, 'peacock' => 1]],
            ]],
        ];
    }

    public static function score(array $answers): string
    {
        $tally = [];
        $qs = self::questions();
        foreach ($answers as $a) {
            [$qi, $oi] = array_pad(explode(':', $a), 2, null);
            $opt = $qs[(int) $qi]['opts'][(int) $oi] ?? null;
            if (! $opt) continue;
            foreach ($opt['w'] as $animal => $w) { $tally[$animal] = ($tally[$animal] ?? 0) + $w; }
        }
        if (! $tally) return 'owl';
        arsort($tally);
        return array_key_first($tally);
    }

    public static function get(string $id): array
    {
        $alias = ['beaver' => 'ant']; // legacy 6-set compatibility
        $id = $alias[$id] ?? $id;
        return self::animals()[$id] ?? self::animals()['owl'];
    }
}
