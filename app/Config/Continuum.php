<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Continuum product configuration.
 *
 * Product name / copy live here — NEVER hard-code "Lumina", "Continuum" or "Talentbank"
 * into domain logic, DB enums or API contracts (01_PRODUCT_CONTRACT.md).
 * No feature flag may bypass authorisation, consent or audit (03_SYSTEM_ARCHITECTURE.md).
 */
class Continuum extends BaseConfig
{
    public string $productName        = 'Continuum';
    public string $productDescriptor  = 'Evidence-to-Outcome Career Operating Layer';
    public string $productTagline     = 'From evidence to outcome.';
    public string $meridianMapName    = 'Meridian Map';
    public string $edgeEngineName     = 'EDGE Evidence Engine';
    public string $passportName       = 'Talentbank Career Passport';

    /** Shown wherever adoption status is relevant. Do not change without adoption approval. */
    public string $adoptionStatus     = 'Continuum — proposed for Talentbank';

    /** Feature flags. Env overrides via env('continuum.<flag>'). */
    public bool   $demoMode           = true;   // enables Scenario Switcher; MUST be false in prod
    public string $passportAdapter    = 'mock'; // mock | talentbank
    public bool   $mentoringEnabled   = true;
    public bool   $trendDataEnabled   = false;  // only true when trend sources are registered

    /** Stale escalation: grace before an overdue update becomes an operator exception. */
    public int    $staleGraceHours    = 24;

    public string $surveyVersion      = 'edge_v2_15q';

    public function __construct()
    {
        parent::__construct();
        // Allow environment overrides without touching code.
        // NOTE: env() returns strings, so "(bool) 'false'" would be TRUE. Parse strictly instead,
        // so continuum.demoMode = false genuinely disables the Scenario Switcher (security).
        $this->demoMode        = self::envBool('continuum.demoMode', $this->demoMode);
        $this->passportAdapter = (string) (env('continuum.passportAdapter', $this->passportAdapter));
        $this->trendDataEnabled= self::envBool('continuum.trendDataEnabled', $this->trendDataEnabled);
        $this->mentoringEnabled= self::envBool('continuum.mentoringEnabled', $this->mentoringEnabled);
    }

    /** Interpret an env flag as a real boolean. Accepts 1/0, true/false, yes/no, on/off (any case). */
    private static function envBool(string $key, bool $default): bool
    {
        $raw = env($key, null);
        if ($raw === null) {
            return $default;
        }
        if (is_bool($raw)) {
            return $raw;
        }
        return in_array(strtolower(trim((string) $raw)), ['1', 'true', 'yes', 'on'], true);
    }
}
