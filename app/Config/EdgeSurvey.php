<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Canonical EDGE survey bank (12_EDGE_SURVEY_BANK.md). ONE versioned source of truth.
 * Replaces all V1 banks. NEVER calls WorkAnimal or any personality service.
 * Reflection choices do NOT award points or imply a better/worse person.
 */
class EdgeSurvey extends BaseConfig
{
    public string $version = 'edge_v2_15q';

    public string $intro =
        'These 15 short reflections help you organise examples of how you approach work. ' .
        'They are not a personality test, diagnosis or hiring decision.';

    public string $skipCopy = 'I have not experienced this yet.';
    public string $nextCta  = 'Add an example when you are ready.';

    /**
     * @var array<int,array{key:string,signal:string,prompt:string}>
     * signal values map to Continuum\CorePolicy\Domain\EdgeSignal.
     */
    public array $questions = [
        ['key' => 'R1', 'signal' => 'reasoning_judgement', 'prompt' => 'When a task is unclear, what is your first step to understand the real problem?'],
        ['key' => 'R2', 'signal' => 'reasoning_judgement', 'prompt' => 'When two options both seem reasonable, how do you compare the trade-offs before deciding?'],
        ['key' => 'R3', 'signal' => 'reasoning_judgement', 'prompt' => 'If information conflicts before a deadline, how do you check it and decide what to do next?'],
        ['key' => 'D1', 'signal' => 'delivery_reliability', 'prompt' => 'When several deadlines overlap, how do you organise the order of work and dependencies?'],
        ['key' => 'D2', 'signal' => 'delivery_reliability', 'prompt' => 'Before you share or submit work, how do you check that it meets the need and can be used by others?'],
        ['key' => 'D3', 'signal' => 'delivery_reliability', 'prompt' => 'If a dependency may delay important work, what early action do you take and who needs to know?'],
        ['key' => 'C1', 'signal' => 'collaboration_communication', 'prompt' => 'If a group is not aligned, how do you help create a shared understanding of the goal and next step?'],
        ['key' => 'C2', 'signal' => 'collaboration_communication', 'prompt' => 'How would you explain technical or complex work to someone with a different level of familiarity?'],
        ['key' => 'C3', 'signal' => 'collaboration_communication', 'prompt' => 'If two people believe they own the same task, how would you clarify roles and move the work forward?'],
        ['key' => 'L1', 'signal' => 'learning_adaptation', 'prompt' => 'When feedback challenges your first idea, how do you understand, test or adapt it?'],
        ['key' => 'L2', 'signal' => 'learning_adaptation', 'prompt' => 'When you need to learn a new tool or process quickly, how do you begin and apply what you learn?'],
        ['key' => 'L3', 'signal' => 'learning_adaptation', 'prompt' => 'If circumstances change and the original plan no longer fits, how do you decide what to keep, change or stop?'],
        ['key' => 'I1', 'signal' => 'initiative_ownership', 'prompt' => 'If you notice a small problem affecting a group or customer, what would you do within the boundaries of your role?'],
        ['key' => 'I2', 'signal' => 'initiative_ownership', 'prompt' => 'If you see a useful improvement but nobody owns it, how would you start responsibly?'],
        ['key' => 'I3', 'signal' => 'initiative_ownership', 'prompt' => 'After an improvement is agreed, how would you help ensure there is an owner, a next step and a way to know whether it helped?'],
    ];
}
