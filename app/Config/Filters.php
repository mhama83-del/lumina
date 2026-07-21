<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\WorkspaceFilter;
use App\Filters\DemoOnlyFilter;

/**
 * V2 security baseline (09_SECURITY_QUALITY_AND_TESTS.md, D-005).
 * V1 shipped with csrf/honeypot/secureheaders commented out — corrected here.
 * CSRF applies to state-changing browser routes; secure headers apply globally.
 */
class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'forcehttps'    => ForceHTTPS::class,
        'workspace'     => WorkspaceFilter::class,
        'demoOnly'      => DemoOnlyFilter::class,
    ];

    public array $required = [
        'before' => ['forcehttps'],
        'after'  => ['secureheaders'],
    ];

    public array $globals = [
        'before' => [
            'honeypot',
            'csrf' => ['except' => ['api/*']], // API uses token auth + its own CSRF strategy later
            'invalidchars',
        ],
        'after' => [
            'honeypot',
            'secureheaders',
        ],
    ];

    public array $methods = [];
    public array $filters = [];
}
