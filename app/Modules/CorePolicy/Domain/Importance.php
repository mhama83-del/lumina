<?php
namespace Continuum\CorePolicy\Domain;

/** Role requirement importance — only these three values (04, 05). */
enum Importance: string
{
    case Critical   = 'critical';
    case Important  = 'important';
    case Supporting = 'supporting';

    public function weight(): int
    {
        return match ($this) {
            self::Critical   => 3,
            self::Important  => 2,
            self::Supporting => 1,
        };
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
