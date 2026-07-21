<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Continuum\CorePolicy\Service\DemoIdentityRegistry;
use Config\Continuum;

/**
 * Route filter enforcing the active identity's role matches the required workspace, so a user only
 * sees their own workspace. Coarse gate; services still run AccessPolicy for resource-level checks.
 * When demoMode is false the demo session is NOT trusted (production auth is required — fail closed).
 */
class WorkspaceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! (new Continuum())->demoMode) {
            // Production: no demo identity may be used. Real auth is not configured yet (fail closed).
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Authentication required.');
        }
        $required = $arguments[0] ?? null;
        $key = session()->get('demo_identity') ?? 'c01_amina';
        $ctx = DemoIdentityRegistry::context($key);
        if ($ctx === null || ($required !== null && $ctx->role->value !== $required)) {
            return redirect()->to('/demo/scenarios')->with('error', 'Select a matching workspace identity.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
