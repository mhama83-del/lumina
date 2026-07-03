<?php

namespace App\Controllers;

class Demo extends BaseController
{
    /**
     * One-click login bypass.
     * $who = "candidate-19-22" | "employer" | "university"
     */
    public function enter(string $who)
    {
        $parts = explode('-', $who, 2);
        $role  = $parts[0];
        $stage = $parts[1] ?? (session('stage') ?? '19-22');

        session()->set([
            'role'    => $role,
            'stage'   => $stage,
            'persona' => $this->personaFor($role, $stage),
        ]);

        return redirect()->to($this->landingFor($role));
    }

    private function landingFor(string $role): string
    {
        return match ($role) {
            'employer'   => base_url('employer'),
            'university' => base_url('university'),
            default      => base_url('candidate'),
        };
    }

    /**
     * Sample hero persona per stage (Fasa 2 placeholder; Fasa 1 seed has the real rows).
     */
    private function personaFor(string $role, string $stage): array
    {
        $map = [
            '16-18'  => ['name' => 'Nurul',  'age' => 17, 'university' => 'Pre-U',  'programme' => 'STEM stream',       'readiness' => 41, 'workAnimal' => 'The Owl'],
            '19-22'  => ['name' => 'Aiman',  'age' => 19, 'university' => 'USM',    'programme' => 'Computer Science',  'readiness' => 72, 'workAnimal' => 'The Owl'],
            '23-28'  => ['name' => 'Wei Jie','age' => 24, 'university' => 'UM',     'programme' => 'Data Science',      'readiness' => 81, 'workAnimal' => 'The Fox'],
            '26-28+' => ['name' => 'Sara',   'age' => 27, 'university' => 'UTM',    'programme' => 'Software Eng',      'readiness' => 88, 'workAnimal' => 'The Eagle'],
        ];
        $p = $map[$stage] ?? $map['19-22'];
        $p['skills'] = ['Python' => 'stated', 'Stakeholder Mgmt' => 'inferred', 'Teamwork' => 'stated'];
        return $p;
    }
}
