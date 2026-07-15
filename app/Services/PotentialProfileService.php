<?php

namespace App\Services;

class PotentialProfileService
{
    private const DOMAINS = ['thinking', 'execution', 'people', 'leadership', 'adaptability', 'learning'];

    private const DOMAIN_LABEL = [
        'thinking'     => 'Thinking',
        'execution'    => 'Execution',
        'people'       => 'People',
        'leadership'   => 'Leadership',
        'adaptability' => 'Adaptability',
        'learning'     => 'Learning',
    ];

    private const MIN_EVIDENCE_LEN = 40;

    public const DISCLAIMER = 'This is a lightweight career potential assessment inspired by '
        . 'behavioural, cognitive-style and work-preference frameworks. It is not a clinical, '
        . 'diagnostic or formally validated psychological test.';

    public function build(array $quizPsTally, string $evidenceText, array $skills): array
    {
        // Strategic C fix: distinguish "quiz answered" from "flat 50-baseline
        // fallback" so the UI can show a clear "not ready yet" state instead
        // of a fake, un-personalised radar chart.
        $hasQuizData = !empty($quizPsTally);

        $scores = $this->normaliseDomainScores($quizPsTally);
        arsort($scores);
        $domainOrder = array_keys($scores);
        $animalBlock = $this->resolveAnimal($evidenceText, $skills);

        return [
            'domains'        => $scores,
            'thinking_style' => $this->thinkingStyleFor($domainOrder[0] ?? 'thinking'),
            'top_strengths'  => $this->labelList(array_slice($domainOrder, 0, 3)),
            'growing_areas'  => $this->labelList(array_slice($domainOrder, 3, 2)),
            'build_next'     => $this->labelList(array_slice(array_reverse($domainOrder), 0, 2)),
            'animal'         => $animalBlock,
            'source'         => $animalBlock !== null ? 'evidence' : 'quiz',
            'disclaimer'     => self::DISCLAIMER,
            'has_quiz_data'  => $hasQuizData,
        ];
    }

    private function normaliseDomainScores(array $tally): array
    {
        $out = [];
        $max = 0;
        foreach (self::DOMAINS as $d) {
            $v = (float) ($tally[$d] ?? 0);
            $out[$d] = $v;
            if ($v > $max) { $max = $v; }
        }
        if ($max <= 0) {
            foreach (self::DOMAINS as $d) { $out[$d] = 50; }
            return $out;
        }
        foreach (self::DOMAINS as $d) {
            $out[$d] = (int) round(30 + (70 * ($out[$d] / $max)));
        }
        return $out;
    }

    private function thinkingStyleFor(string $topDomain): string
    {
        return match ($topDomain) {
            'thinking'     => 'Structured Thinker',
            'execution'    => 'Action-Oriented Thinker',
            'people'       => 'People-Centred Thinker',
            'leadership'   => 'People-Centred Thinker',
            'adaptability' => 'Exploratory Thinker',
            'learning'     => 'Exploratory Thinker',
            default        => 'Structured Thinker',
        };
    }

    private function labelList(array $domainKeys): array
    {
        return array_values(array_map(fn ($d) => self::DOMAIN_LABEL[$d] ?? $d, $domainKeys));
    }

    private function resolveAnimal(string $evidenceText, array $skills): ?array
    {
        if (mb_strlen(trim($evidenceText)) < self::MIN_EVIDENCE_LEN) {
            return null;
        }
        return (new AnimalInferenceService())->infer($skills, $evidenceText);
    }
}
