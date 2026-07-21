<?php
namespace App\Controllers;

use Continuum\CorePolicy\Service\DemoIdentityRegistry;

/** Scenario Switcher — DEMO_MODE only. Context swap, NEVER authorization (D-006). */
class Demo extends ContinuumController
{
    public function scenarios()
    {
        return $this->shell('demo_scenarios', ['identities' => DemoIdentityRegistry::all()], 'demo');
    }

    public function enter(string $key)
    {
        if (DemoIdentityRegistry::context($key) === null) {
            return redirect()->to('/demo/scenarios')->with('error', 'Unknown demo identity.');
        }
        session()->set('demo_identity', $key);
        $ctx = DemoIdentityRegistry::context($key);
        // Route to the matching workspace home.
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
        session()->remove('demo_identity');
        return redirect()->to('/demo/scenarios');
    }
}
