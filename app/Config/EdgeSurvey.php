<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Canonical EDGE survey bank (12_EDGE_SURVEY_BANK.md). ONE versioned source of truth.
 * Replaces all V1 banks. NEVER calls WorkAnimal or any personality service.
 *
 * IMPORTANT (product contract): this is NOT a personality/archetype test. Options are different
 * VALID approaches to work — none is "better". A chosen option is stored only to (a) record that the
 * candidate reflected on that EDGE area (drives Meridian reflection coverage) and (b) prompt an
 * evidence example. Choices are NEVER scored, ranked, or turned into a personality result. The EDGE
 * signal each question maps to is intentionally hidden from the respondent in the UI.
 */
class EdgeSurvey extends BaseConfig
{
    public string $version = 'edge_v2_15q';

    public string $intro =
        'Answer quickly and honestly — pick the option closest to how you usually work. ' .
        'There are no right or wrong answers, and this is not a personality test or a score.';

    public string $skipCopy = 'Skip';
    public string $nextCta  = 'See my map';

    /**
     * @var array<int,array{key:string,signal:string,prompt:string,options:string[]}>
     * signal maps to Continuum\CorePolicy\Domain\EdgeSignal — hidden from the UI.
     */
    public array $questions = [
        ['key'=>'R1','signal'=>'reasoning_judgement','prompt'=>'When a task is unclear, your first move is usually to…',
         'options'=>['Ask questions to whoever set it','Break it into smaller parts','Look at similar past examples','List what is known vs unknown']],
        ['key'=>'R2','signal'=>'reasoning_judgement','prompt'=>'Facing two reasonable options, you tend to…',
         'options'=>['Weigh the trade-offs on paper','Try a quick small test','Ask someone with more context','Pick and adjust as you learn']],
        ['key'=>'R3','signal'=>'reasoning_judgement','prompt'=>'When information conflicts before a deadline, you…',
         'options'=>['Check the most trusted source','Flag it and decide with what you have','Ask the person affected','Note the risk and move on']],
        ['key'=>'D1','signal'=>'delivery_reliability','prompt'=>'With several overlapping deadlines, you usually…',
         'options'=>['Order by dependency','Order by impact','Do quick wins first','Ask which matters most']],
        ['key'=>'D2','signal'=>'delivery_reliability','prompt'=>'Before you submit work, you most often…',
         'options'=>['Re-check against the need','Ask someone to review','Test it end to end','Ship and refine later']],
        ['key'=>'D3','signal'=>'delivery_reliability','prompt'=>'If a dependency might delay important work, you…',
         'options'=>['Tell whoever is affected early','Find a workaround first','Re-plan the timeline','Escalate to the owner']],
        ['key'=>'C1','signal'=>'collaboration_communication','prompt'=>'When a group is not aligned, you tend to…',
         'options'=>['Restate the shared goal','Write down the next step','Ask each person their view','Suggest a quick decision']],
        ['key'=>'C2','signal'=>'collaboration_communication','prompt'=>'Explaining complex work to a non-expert, you…',
         'options'=>['Use a simple analogy','Start from why it matters','Show a small example','Avoid jargon and check understanding']],
        ['key'=>'C3','signal'=>'collaboration_communication','prompt'=>'If two people think they own the same task, you…',
         'options'=>['Clarify roles openly','Ask a lead to decide','Split it clearly','Focus on the next step together']],
        ['key'=>'L1','signal'=>'learning_adaptation','prompt'=>'When feedback challenges your first idea, you…',
         'options'=>['Ask what they saw','Test the alternative','Keep what works, change the rest','Sit with it before deciding']],
        ['key'=>'L2','signal'=>'learning_adaptation','prompt'=>'Needing to learn a new tool fast, you…',
         'options'=>['Try it hands-on','Follow a short guide','Ask someone who knows','Learn just enough to start']],
        ['key'=>'L3','signal'=>'learning_adaptation','prompt'=>'When the plan no longer fits, you decide what to…',
         'options'=>['Keep, change or stop by impact','Ask the team first','Protect the core goal','Restart small']],
        ['key'=>'I1','signal'=>'initiative_ownership','prompt'=>'Noticing a small problem affecting others, you…',
         'options'=>['Fix it within your role','Flag it to the owner','Suggest an improvement','Check if others noticed']],
        ['key'=>'I2','signal'=>'initiative_ownership','prompt'=>'Seeing a useful improvement nobody owns, you…',
         'options'=>['Start it responsibly','Propose it first','Find who should own it','Pilot it small']],
        ['key'=>'I3','signal'=>'initiative_ownership','prompt'=>'After an improvement is agreed, you help ensure…',
         'options'=>['There is a clear owner','There is a next step','There is a way to measure it','Someone follows up']],
    ];
}
