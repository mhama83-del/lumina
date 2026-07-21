<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/** Explicit CORS allowlist (09). No wildcard origins. */
class Cors extends BaseConfig
{
    public array $default = [
        'allowedOrigins'         => ['http://localhost:8080'],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => true,
        'allowedHeaders'         => ['Content-Type', 'X-CSRF-TOKEN', 'X-Requested-With'],
        'exposedHeaders'         => [],
        'allowedMethods'         => ['GET', 'POST'],
        'maxAge'                 => 3600,
    ];
}
