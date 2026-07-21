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
        $this->ctx     = $this->resolveContext();
    }

    /**
     * Identity resolution. In DEMO_MODE the Scenario Switcher session decides who you view. When
     * DEMO_MODE is false the demo session is NOT trusted — production must resolve identity from a
     * real authenticated session (see resolveProductionContext()).
     */
    protected function resolveContext(): PolicyContext
    {
        if ($this->product->demoMode) {
            $key = session()->get('demo_identity') ?? 'c01_amina';
            return DemoIdentityRegistry::context($key, true)
                ?? DemoIdentityRegistry::context('c01_amina', true);
        }
        return $this->resolveProductionContext();
    }

    /**
     * Production identity resolver. NOT IMPLEMENTED — no production authentication exists yet.
     * Intentionally fails closed (throws) so a non-demo deployment cannot silently fall back to a
     * demo persona. A real resolver must read an authenticated user + their tenant/role.
     */
    protected function resolveProductionContext(): PolicyContext
    {
        throw new \RuntimeException(
            'Production authentication is not configured. Set continuum.demoMode = true for the demo, '
            . 'or implement resolveProductionContext() against a real auth session.'
        );
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
