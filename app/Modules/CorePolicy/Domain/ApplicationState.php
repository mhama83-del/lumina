<?php
namespace Continuum\CorePolicy\Domain;

/** Application lifecycle states (06_OUTCOME_LOOP.md). */
enum ApplicationState: string
{
    case Draft                 = 'draft';
    case Submitted             = 'submitted';
    case Received              = 'received';
    case UnderReview           = 'under_review';
    case ClarificationRequested= 'clarification_requested';
    case InterviewInvited      = 'interview_invited';
    case InterviewScheduled    = 'interview_scheduled';
    case InterviewCompleted    = 'interview_completed';
    case DecisionPending       = 'decision_pending';
    case OfferMade             = 'offer_made';
    case OfferAccepted         = 'offer_accepted';
    case OfferDeclined         = 'offer_declined';
    case NotSelected           = 'not_selected';
    case Withdrawn             = 'withdrawn';
    case ExpiredClosed         = 'expired_closed';

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::OfferAccepted, self::OfferDeclined, self::NotSelected,
            self::Withdrawn, self::ExpiredClosed,
        ], true);
    }

    /** Whether an active (non-terminal, non-draft) application must carry owner + expected update. */
    public function requiresOwnerAndExpectedUpdate(): bool
    {
        return $this !== self::Draft && ! $this->isTerminal();
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
