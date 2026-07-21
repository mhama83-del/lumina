<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Continuum\CorePolicy\Service\PolicyContext;
use Continuum\CorePolicy\Service\DemoIdentityRegistry;
use Continuum\CorePolicy\Service\AuditService;
use Config\Continuum as ContinuumConfig;

/** Base controller: resolves the active (demo) PolicyContext and shares product config to views. */
abstract class ContinuumController extends Controller
{
    protected PolicyContext $ctx;
    protected ContinuumConfig $product;
    protected $db;
    protected AuditService $audit;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        $this->product = new ContinuumConfig();
        $this->db      = \Config\Database::connect();
        $this->audit   = new AuditService($this->db);
        $key = session()->get('demo_identity') ?? 'c01_amina';
        $this->ctx = DemoIdentityRegistry::context($key, $this->product->demoMode)
            ?? DemoIdentityRegistry::context('c01_amina');
    }

    protected function shell(string $view, array $data = [], string $workspace = 'candidate'): string
    {
        $data['product']   = $this->product;
        $data['ctx']       = $this->ctx;
        $data['workspace'] = $workspace;
        $data['demoMode']  = $this->product->demoMode;
        return view('continuum/' . $view, $data);
    }
}
