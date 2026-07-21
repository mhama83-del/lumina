<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Continuum;

/** Blocks demo routes unless DEMO_MODE is on. The switcher is never a production control. */
class DemoOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! (new Continuum())->demoMode) {
            return redirect()->to('/')->with('error', 'Demo mode is disabled.');
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
