<?php
namespace Continuum\Evidence\Domain;

use Continuum\CorePolicy\Domain\EdgeSignal;

/**
 * Meridian Map aggregation (05 §2, 12 §Map aggregation). Framework-independent.
 *
 * Produces TWO layers per signal and always exposes a text alternative:
 *   - Dashed (reflection): answered prompts / 3  — "reflection coverage", NOT ability.
 *   - Filled (evidence):   source-backed evidence coverage for claims linked to the signal.
 *
 * NEVER emits a "high/low" verdict, personality score or 0-100 signal-strength badge.
 */
final class MeridianMapService
{
    /**
     * @param array<string,int> $answeredPromptsPerSignal signal.value => count (0..3)
     * @param array<string,array{covered:int,total:int}> $evidenceCoveragePerSignal
     *        signal.value => ['covered'=>source-backed claims, 'total'=>claims linked]
     * @return array{legend:array,axes:array,text_alternative:array}
     */
    public function build(array $answeredPromptsPerSignal, array $evidenceCoveragePerSignal): array
    {
        $axes = [];
        $textRows = [];
        foreach (EdgeSignal::ordered() as $signal) {
            $answered = max(0, min(3, $answeredPromptsPerSignal[$signal->value] ?? 0));
            $reflection = round($answered / 3, 3);          // 0..1 completion, described not scored

            $cov = $evidenceCoveragePerSignal[$signal->value] ?? ['covered' => 0, 'total' => 0];
            $total = max(0, (int) $cov['total']);
            $covered = max(0, min($total, (int) $cov['covered']));
            $evidence = $total > 0 ? round($covered / $total, 3) : 0.0;

            $axes[] = [
                'signal'            => $signal->value,
                'label'             => $signal->label(),
                'reflection_layer'  => $reflection,        // dashed
                'evidence_layer'    => $evidence,          // filled
                'answered_prompts'  => $answered,
                'evidence_covered'  => $covered,
                'evidence_total'    => $total,
                'empty'             => $answered === 0 && $total === 0,
            ];
            $textRows[] = [
                'area'      => $signal->label(),
                'reflection'=> $answered === 0 ? 'Not yet discussed' : "{$answered} of 3 prompts discussed",
                'evidence'  => $total === 0 ? 'Evidence can be added' : "{$covered} of {$total} claims source-backed",
            ];
        }

        return [
            'legend' => [
                'dashed' => 'Self-described reflection: EDGE areas you have discussed in the survey.',
                'filled' => 'Evidence coverage: claims linked to a source you approved.',
                'note'   => 'This is not an ability, personality or employability score.',
            ],
            'axes'             => $axes,
            'text_alternative' => $textRows,
        ];
    }
}
