<?php

namespace App\Libraries;

/**
 * WorkAnimal (Fasa 6) — self-discovery onboarding, 12 archetypes.
 * Strategic B1: each option now also carries a 'ps' (Potential Signal) weight
 * across six domains, read by App\Services\PotentialProfileService.
 * Additive: score() still only reads 'w', unchanged behaviour.
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

    public static function questions(): array
    {
        return [
            ['q' => 'I feel most energised when I…', 'opts' => [
                ['t' => 'Solve a tricky puzzle',        'w' => ['owl' => 2],               'ps' => ['thinking' => 2]],
                ['t' => 'Try something new fast',       'w' => ['cheetah' => 1, 'fox' => 1],'ps' => ['learning' => 2]],
                ['t' => 'Lead a team forward',          'w' => ['lion' => 1, 'wolf' => 1],  'ps' => ['leadership' => 2]],
                ['t' => 'Help other people',            'w' => ['dolphin' => 2],            'ps' => ['people' => 2]],
                ['t' => 'Build something with my hands','w' => ['octopus' => 1, 'ant' => 1],'ps' => ['execution' => 2]],
            ]],
            ['q' => 'People often describe me as…', 'opts' => [
                ['t' => 'Analytical',  'w' => ['owl' => 2],                  'ps' => ['thinking' => 2]],
                ['t' => 'Visionary',   'w' => ['eagle' => 2],                'ps' => ['leadership' => 1, 'learning' => 1]],
                ['t' => 'Caring',      'w' => ['elephant' => 1, 'dolphin' => 1], 'ps' => ['people' => 2]],
                ['t' => 'Reliable',    'w' => ['horse' => 1, 'ant' => 1],    'ps' => ['execution' => 2]],
                ['t' => 'Expressive',  'w' => ['peacock' => 2],              'ps' => ['people' => 1, 'adaptability' => 1]],
            ]],
            ['q' => 'I prefer to work with…', 'opts' => [
                ['t' => 'Data and ideas',       'w' => ['owl' => 2],                 'ps' => ['thinking' => 2]],
                ['t' => 'Products and markets', 'w' => ['fox' => 2],                 'ps' => ['adaptability' => 1, 'execution' => 1]],
                ['t' => 'A team I lead',         'w' => ['lion' => 1, 'wolf' => 1],   'ps' => ['leadership' => 2]],
                ['t' => 'People directly',       'w' => ['dolphin' => 1, 'peacock' => 1], 'ps' => ['people' => 2]],
                ['t' => 'Systems and process',   'w' => ['ant' => 2],                 'ps' => ['execution' => 2]],
            ]],
            ['q' => 'Facing a hard problem, I…', 'opts' => [
                ['t' => 'Analyse it deeply',    'w' => ['owl' => 2],                 'ps' => ['thinking' => 2]],
                ['t' => 'Improvise a way',      'w' => ['fox' => 1, 'cheetah' => 1], 'ps' => ['adaptability' => 2]],
                ['t' => 'Decide fast and lead', 'w' => ['lion' => 2],                'ps' => ['leadership' => 2]],
                ['t' => 'Talk it through',      'w' => ['dolphin' => 1, 'elephant' => 1], 'ps' => ['people' => 2]],
                ['t' => 'Follow a method',      'w' => ['horse' => 1, 'ant' => 1],   'ps' => ['execution' => 2]],
            ]],
            ['q' => 'The impact I want is…', 'opts' => [
                ['t' => 'Sharp insight',         'w' => ['owl' => 2],                 'ps' => ['thinking' => 2]],
                ['t' => 'A bold new venture',    'w' => ['eagle' => 1, 'cheetah' => 1], 'ps' => ['leadership' => 1, 'adaptability' => 1]],
                ['t' => 'Build something big',   'w' => ['lion' => 1, 'wolf' => 1],   'ps' => ['execution' => 1, 'leadership' => 1]],
                ['t' => 'Bring people together', 'w' => ['dolphin' => 1, 'elephant' => 1], 'ps' => ['people' => 2]],
                ['t' => 'Craft something great', 'w' => ['octopus' => 1, 'peacock' => 1], 'ps' => ['execution' => 1, 'learning' => 1]],
            ]],
            ['q' => 'When facing an unfamiliar task, what do you usually do first?', 'opts' => [
                ['t' => 'Break it into structured steps',           'w' => ['owl' => 1, 'ant' => 1],    'ps' => ['thinking' => 2]],
                ['t' => 'Ask others who might know',                'w' => ['dolphin' => 1, 'elephant' => 1], 'ps' => ['people' => 2]],
                ['t' => 'Try something and adjust as I go',         'w' => ['fox' => 1, 'cheetah' => 1], 'ps' => ['learning' => 2]],
                ['t' => 'Look for a similar past experience to reuse', 'w' => ['horse' => 1, 'ant' => 1], 'ps' => ['execution' => 1, 'learning' => 1]],
            ]],
            ['q' => 'In a team project, I naturally end up…', 'opts' => [
                ['t' => 'Organising who does what',            'w' => ['wolf' => 1, 'lion' => 1], 'ps' => ['leadership' => 2]],
                ['t' => "Making sure details don't slip through", 'w' => ['ant' => 1, 'owl' => 1], 'ps' => ['execution' => 2]],
                ['t' => 'Keeping everyone motivated and connected', 'w' => ['dolphin' => 1, 'peacock' => 1], 'ps' => ['people' => 2]],
                ['t' => 'Coming up with the big idea',         'w' => ['eagle' => 1, 'fox' => 1], 'ps' => ['thinking' => 1, 'leadership' => 1]],
            ]],
            ['q' => 'When plans change suddenly, I…', 'opts' => [
                ['t' => 'Stay calm and adjust the plan',           'w' => ['horse' => 1, 'fox' => 1], 'ps' => ['adaptability' => 2]],
                ['t' => 'Feel unsettled but push through',         'w' => ['elephant' => 1],           'ps' => ['adaptability' => 1, 'execution' => 1]],
                ['t' => 'See it as a chance to try something new', 'w' => ['cheetah' => 1, 'octopus' => 1], 'ps' => ['adaptability' => 1, 'learning' => 1]],
                ['t' => 'Take charge to steady the group',         'w' => ['lion' => 1, 'wolf' => 1], 'ps' => ['leadership' => 2]],
            ]],
            ['q' => 'I learn best when I…', 'opts' => [
                ['t' => 'Study the underlying theory first', 'w' => ['owl' => 1],               'ps' => ['thinking' => 2]],
                ['t' => 'Get hands-on and experiment',        'w' => ['octopus' => 1, 'cheetah' => 1], 'ps' => ['learning' => 2]],
                ['t' => 'Learn alongside others',             'w' => ['dolphin' => 1, 'elephant' => 1], 'ps' => ['people' => 1, 'learning' => 1]],
                ['t' => 'Follow a clear step-by-step guide',  'w' => ['ant' => 1, 'horse' => 1], 'ps' => ['execution' => 1, 'thinking' => 1]],
            ]],
            ['q' => 'When I disagree with a group decision, I…', 'opts' => [
                ['t' => 'Present data or logic to make my case',       'w' => ['owl' => 1],          'ps' => ['thinking' => 2]],
                ['t' => 'Raise it privately with the people involved', 'w' => ['elephant' => 1, 'dolphin' => 1], 'ps' => ['people' => 2]],
                ['t' => 'State my position clearly and push for change', 'w' => ['lion' => 1, 'eagle' => 1], 'ps' => ['leadership' => 2]],
                ['t' => 'Go along with it but note the risk for later', 'w' => ['horse' => 1],        'ps' => ['adaptability' => 1]],
            ]],
            ['q' => 'A setback or failure usually makes me…', 'opts' => [
                ['t' => 'Analyse what went wrong in detail', 'w' => ['owl' => 1],               'ps' => ['thinking' => 1, 'learning' => 1]],
                ['t' => 'Bounce back quickly and try again', 'w' => ['cheetah' => 1, 'fox' => 1], 'ps' => ['adaptability' => 2]],
                ['t' => 'Lean on people around me',          'w' => ['dolphin' => 1, 'elephant' => 1], 'ps' => ['people' => 2]],
                ['t' => 'Double down and push through it',   'w' => ['lion' => 1, 'ant' => 1],   'ps' => ['execution' => 2]],
            ]],
            ['q' => 'Given a leadership opportunity, I…', 'opts' => [
                ['t' => 'Take it and set the direction',                 'w' => ['lion' => 1, 'eagle' => 1], 'ps' => ['leadership' => 2]],
                ['t' => 'Take it but focus on supporting the team',      'w' => ['wolf' => 1, 'elephant' => 1], 'ps' => ['leadership' => 1, 'people' => 1]],
                ['t' => 'Prefer to contribute expertise instead',        'w' => ['owl' => 1, 'ant' => 1],    'ps' => ['thinking' => 1, 'execution' => 1]],
                ['t' => 'Take it as a chance to try a new way of working', 'w' => ['fox' => 1, 'octopus' => 1], 'ps' => ['leadership' => 1, 'learning' => 1]],
            ]],
            ['q' => 'What matters most in the work I do?', 'opts' => [
                ['t' => 'Getting it exactly right',        'w' => ['ant' => 1, 'owl' => 1],    'ps' => ['execution' => 1, 'thinking' => 1]],
                ['t' => 'Making a difference to people',    'w' => ['dolphin' => 1, 'elephant' => 1], 'ps' => ['people' => 2]],
                ['t' => 'Moving fast and shipping something', 'w' => ['cheetah' => 1, 'fox' => 1], 'ps' => ['execution' => 1, 'adaptability' => 1]],
                ['t' => 'Growing and learning new things',  'w' => ['octopus' => 1, 'eagle' => 1], 'ps' => ['learning' => 2]],
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

    public static function psScore(array $answers): array
    {
        $tally = [];
        $qs = self::questions();
        foreach ($answers as $a) {
            [$qi, $oi] = array_pad(explode(':', $a), 2, null);
            $opt = $qs[(int) $qi]['opts'][(int) $oi] ?? null;
            if (! $opt) continue;
            foreach (($opt['ps'] ?? []) as $domain => $w) {
                $tally[$domain] = ($tally[$domain] ?? 0) + $w;
            }
        }
        return $tally;
    }

    public static function get(string $id): array
    {
        $alias = ['beaver' => 'ant'];
        $id = $alias[$id] ?? $id;
        return self::animals()[$id] ?? self::animals()['owl'];
    }
}
