<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Continuum\CorePolicy\Service\DemoIdentityRegistry;

/**
 * Route filter enforcing the active demo identity's role matches the required workspace.
 * A user sees only their workspace. This is a coarse gate; services still run AccessPolicy.
 */
class WorkspaceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $required = $arguments[0] ?? null;
        $key = session()->get('demo_identity') ?? 'c01_amina';
        $ctx = DemoIdentityRegistry::context($key);
        if ($ctx === null || ($required !== null && $ctx->role->value !== $required)) {
            return redirect()->to('/demo/scenarios')->with('error', 'Select a matching workspace identity.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
