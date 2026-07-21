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
        $this->demoMode        = (bool) (env('continuum.demoMode', $this->demoMode));
        $this->passportAdapter = (string) (env('continuum.passportAdapter', $this->passportAdapter));
        $this->trendDataEnabled= (bool) (env('continuum.trendDataEnabled', $this->trendDataEnabled));
    }
}
