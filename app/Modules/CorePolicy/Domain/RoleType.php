<?php
namespace Continuum\CorePolicy\Domain;

/** Workspace roles. A user sees only their workspace (01_PRODUCT_CONTRACT.md). */
enum RoleType: string
{
    case Candidate = 'candidate';
    case Employer  = 'employer';
    case University = 'university';
    case Operator  = 'operator';   // Talentbank operator
    case Mentor    = 'mentor';     // support layer (P1)

    public function label(): string
    {
        return match ($this) {
            self::Candidate  => 'Candidate',
            self::Employer   => 'Employer',
            self::University => 'University',
            self::Operator   => 'Talentbank operator',
            self::Mentor     => 'Mentor',
        };
    }
}
