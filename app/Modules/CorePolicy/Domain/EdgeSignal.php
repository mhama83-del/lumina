<?php
namespace Continuum\CorePolicy\Domain;

/** The five EDGE signals — organisation of evidence, NOT personality (02, 05). */
enum EdgeSignal: string
{
    case ReasoningJudgement       = 'reasoning_judgement';
    case DeliveryReliability      = 'delivery_reliability';
    case CollaborationCommunication = 'collaboration_communication';
    case LearningAdaptation       = 'learning_adaptation';
    case InitiativeOwnership      = 'initiative_ownership';

    public function label(): string
    {
        return match ($this) {
            self::ReasoningJudgement        => 'Reasoning & Judgement',
            self::DeliveryReliability       => 'Delivery & Reliability',
            self::CollaborationCommunication=> 'Collaboration & Communication',
            self::LearningAdaptation        => 'Learning & Adaptation',
            self::InitiativeOwnership       => 'Initiative & Ownership',
        };
    }

    /** @return self[] canonical display order */
    public static function ordered(): array
    {
        return [
            self::ReasoningJudgement,
            self::DeliveryReliability,
            self::CollaborationCommunication,
            self::LearningAdaptation,
            self::InitiativeOwnership,
        ];
    }
}
