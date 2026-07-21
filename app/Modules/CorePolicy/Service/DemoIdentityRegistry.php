<?php
namespace Continuum\CorePolicy\Service;

use Continuum\CorePolicy\Domain\RoleType;

/**
 * The 15 demo identities (07_UX_UI_AND_PERSONAS.md). Used by the Scenario Switcher to swap the
 * active PolicyContext in DEMO_MODE only. subjectId/tenantId align with the DemoFixtures seeder.
 */
final class DemoIdentityRegistry
{
    /** @return array<string,array{role:RoleType,subject:int,tenant:?int,name:string,discipline:string}> */
    public static function all(): array
    {
        return [
            'c01_amina' => ['role'=>RoleType::Candidate,'subject'=>1,'tenant'=>null,'name'=>'Amina','discipline'=>'Data Analytics'],
            'c02_daniel'=> ['role'=>RoleType::Candidate,'subject'=>2,'tenant'=>null,'name'=>'Daniel','discipline'=>'Software Engineering'],
            'c03_siti'  => ['role'=>RoleType::Candidate,'subject'=>3,'tenant'=>null,'name'=>'Siti','discipline'=>'UX/UI Design'],
            'c04_aaron' => ['role'=>RoleType::Candidate,'subject'=>4,'tenant'=>null,'name'=>'Aaron','discipline'=>'Finance & Accounting'],
            'c05_priya' => ['role'=>RoleType::Candidate,'subject'=>5,'tenant'=>null,'name'=>'Priya','discipline'=>'Mechanical/Manufacturing Engineering'],
            'c06_hafiz' => ['role'=>RoleType::Candidate,'subject'=>6,'tenant'=>null,'name'=>'Hafiz','discipline'=>'Electrical/IoT Engineering'],
            'c07_mei'   => ['role'=>RoleType::Candidate,'subject'=>7,'tenant'=>null,'name'=>'Mei','discipline'=>'Supply Chain & Logistics'],
            'c08_farid' => ['role'=>RoleType::Candidate,'subject'=>8,'tenant'=>null,'name'=>'Farid','discipline'=>'Marketing & Communications'],
            'c09_nadia' => ['role'=>RoleType::Candidate,'subject'=>9,'tenant'=>null,'name'=>'Nadia','discipline'=>'Public Health / Healthcare'],
            'c10_jason' => ['role'=>RoleType::Candidate,'subject'=>10,'tenant'=>null,'name'=>'Jason','discipline'=>'Education / People Operations'],
            'e01_nova'  => ['role'=>RoleType::Employer,'subject'=>1,'tenant'=>1,'name'=>'Nova Digital','discipline'=>'Digital & Data'],
            'e02_apex'  => ['role'=>RoleType::Employer,'subject'=>2,'tenant'=>2,'name'=>'Apex Engineering','discipline'=>'Engineering & Manufacturing'],
            'e03_harbor'=> ['role'=>RoleType::Employer,'subject'=>3,'tenant'=>3,'name'=>'Harbor Services','discipline'=>'Business & Consumer Services'],
            'u01_university'=> ['role'=>RoleType::University,'subject'=>1,'tenant'=>10,'name'=>'University Operator','discipline'=>'Institution'],
            't01_operator'  => ['role'=>RoleType::Operator,'subject'=>1,'tenant'=>null,'name'=>'Talentbank Operator','discipline'=>'Ecosystem'],
        ];
    }

    public static function context(string $key, bool $demo = true): ?PolicyContext
    {
        $all = self::all();
        if (! isset($all[$key])) {
            return null;
        }
        $i = $all[$key];
        return new PolicyContext($key, $i['role'], $i['subject'], $i['tenant'], $demo);
    }
}
