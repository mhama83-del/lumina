<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * V2 autoload. Adds the Continuum module namespace so each module owns its own tree.
 * Merge these psr4 entries into your existing app/Config/Autoload.php if you already customised it.
 */
class Autoload extends AutoloadConfig
{
    public $psr4 = [
        APP_NAMESPACE      => APPPATH,                 // App\
        'Config'           => APPPATH . 'Config',
        'Continuum'        => APPPATH . 'Modules',     // Continuum\<Module>\...  -> app/Modules/<Module>/...
    ];

    public $classmap = [];
    public $files    = [];
    public $helpers  = [];
}
