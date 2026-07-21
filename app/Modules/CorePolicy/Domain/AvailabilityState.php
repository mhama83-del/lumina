<?php
namespace Continuum\CorePolicy\Domain;

/** Time-bounded availability pulse (06_OUTCOME_LOOP.md). A gate, never a merit point. */
enum AvailabilityState: string
{
    case ActivelyAvailable = 'actively_available';
    case OpenToOpportunity = 'open_to_opportunity';
    case Unavailable       = 'unavailable';
    case UnknownStale      = 'unknown_stale';

    public function isActive(): bool
    {
        return $this === self::ActivelyAvailable || $this === self::OpenToOpportunity;
    }

    public function label(): string
    {
        return match ($this) {
            self::ActivelyAvailable => 'Actively available',
            self::OpenToOpportunity => 'Open to opportunity',
            self::Unavailable       => 'Unavailable',
            self::UnknownStale      => 'Unknown / stale',
        };
    }
}
