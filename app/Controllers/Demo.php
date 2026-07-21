<?php
namespace App\Controllers;

use Continuum\CorePolicy\Service\DemoIdentityRegistry;

/** Scenario Switcher — DEMO_MODE only. Context swap, NEVER authorization (D-006). */
class Demo extends ContinuumController
{
    /** Fail closed when DEMO_MODE is off: the switcher must not exist in production. */
    private function guardDemo()
    {
        if (! $this->product->demoMode) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Scenario Switcher is disabled.');
        }
        return null;
    }

    public function scenarios()
    {
        $this->guardDemo();
        return $this->shell('demo_scenarios', ['identities' => DemoIdentityRegistry::all()], 'demo');
    }

    public function enter(string $key)
    {
        $this->guardDemo();
        if (DemoIdentityRegistry::context($key) === null) {
            return redirect()->to('/demo/scenarios')->with('error', 'Unknown demo identity.');
        }
        session()->set('demo_identity', $key);
        $ctx = DemoIdentityRegistry::context($key);
        return redirect()->to(match ($ctx->role->value) {
            'candidate'  => '/candidate/home',
            'employer'   => '/employer/roles',
            'university' => '/university/cohorts/1',
            'operator'   => '/operator/control-tower',
            default      => '/',
        });
    }

    public function reset()
    {
        $this->guardDemo();
        session()->remove('demo_identity');
        return redirect()->to('/demo/scenarios');
    }
}
