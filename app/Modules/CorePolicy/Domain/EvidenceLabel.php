<?php
namespace Continuum\CorePolicy\Domain;

/**
 * Evidence provenance labels and their RER sufficiency ceiling (02, 05).
 * Inferred only counts as 1 AFTER candidate confirmation — enforced by EvidenceLabelPolicy,
 * not by this enum alone.
 */
enum EvidenceLabel: string
{
    case NeedsEvidence = 'needs_evidence';
    case Stated        = 'stated';
    case Inferred      = 'inferred';
    case Supported     = 'supported';
    case HumanVerified = 'human_verified';

    public function label(): string
    {
        return match ($this) {
            self::NeedsEvidence => 'Needs Evidence',
            self::Stated        => 'Stated',
            self::Inferred      => 'Inferred',
            self::Supported     => 'Supported',
            self::HumanVerified => 'Human Verified',
        };
    }
}
