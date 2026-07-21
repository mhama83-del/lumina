<?php
namespace Continuum\CorePolicy\Domain;

/**
 * Employer review queue GATE labels — never a rank or sorted score band (05 §4).
 * Consent and availability are gates, never positive points.
 */
enum QueueLabel: string
{
    case ReviewNow              = 'review_now';
    case ReviewWithQuestions    = 'review_with_questions';
    case CandidateActionSuggested = 'candidate_action_suggested';

    public function label(): string
    {
        return match ($this) {
            self::ReviewNow                => 'Review now',
            self::ReviewWithQuestions      => 'Review with questions',
            self::CandidateActionSuggested => 'Candidate action suggested',
        };
    }
}
