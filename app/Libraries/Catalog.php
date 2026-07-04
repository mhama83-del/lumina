<?php

namespace App\Libraries;

/**
 * Shared roles catalog for Smart Matching + Employer + Resume Analysis.
 * Spans multiple domains so different profiles match different roles.
 */
class Catalog
{
    public static function roles(): array
    {
        return [
            'data_analyst'      => ['key'=>'data_analyst','title'=>'Data Analyst','company'=>'Maybank','location'=>'KL','salary'=>'RM6–8k','domain'=>'Data','color'=>'var(--indigo)','hex'=>'#6C5CE7','required'=>['sql','dashboarding','python','data_analysis']],
            'ml_engineer'       => ['key'=>'ml_engineer','title'=>'ML Engineer','company'=>'Petronas','location'=>'KL','salary'=>'RM9–12k','domain'=>'Data','color'=>'var(--indigo)','hex'=>'#6C5CE7','required'=>['python','machine_learning','statistics','sql']],
            'software_engineer' => ['key'=>'software_engineer','title'=>'Software Engineer','company'=>'Grab','location'=>'KL','salary'=>'RM8–11k','domain'=>'Engineering','color'=>'var(--teal)','hex'=>'#14B8A6','required'=>['software','javascript','api','communication']],
            'backend_engineer'  => ['key'=>'backend_engineer','title'=>'Backend Engineer','company'=>'CIMB','location'=>'KL','salary'=>'RM8–10k','domain'=>'Engineering','color'=>'var(--teal)','hex'=>'#14B8A6','required'=>['software','cloud','java','api']],
            'ux_designer'       => ['key'=>'ux_designer','title'=>'UX Designer','company'=>'AirAsia','location'=>'KL','salary'=>'RM5–8k','domain'=>'Design','color'=>'var(--violet)','hex'=>'#a78bfa','required'=>['ui_ux','figma','design_thinking','communication']],
            'marketing_exec'    => ['key'=>'marketing_exec','title'=>'Marketing Executive','company'=>'Nestlé','location'=>'Selangor','salary'=>'RM4–6k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['marketing','social_media','content','communication']],
            'product_exec'      => ['key'=>'product_exec','title'=>'Product Executive','company'=>'Shopee','location'=>'KL','salary'=>'RM6–9k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['stakeholder_mgmt','communication','leadership','project_mgmt']],
            'accountant'        => ['key'=>'accountant','title'=>'Accountant','company'=>'KPMG','location'=>'KL','salary'=>'RM5–7k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['accounting','finance','excel','communication']],
            'sales_exec'        => ['key'=>'sales_exec','title'=>'Sales Executive','company'=>'Maxis','location'=>'KL','salary'=>'RM4–7k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['sales','communication','customer_service']],
            'content_creator'   => ['key'=>'content_creator','title'=>'Content Strategist','company'=>'Astro','location'=>'KL','salary'=>'RM4–6k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['content','social_media','writing','seo']],
        ];
    }

    public static function role(string $key): array
    {
        $r = self::roles();
        return $r[$key] ?? array_values($r)[0];
    }

    public static function label(string $c): string
    {
        $m = [
            'sql'=>'SQL','dashboarding'=>'Dashboarding','python'=>'Python','data_analysis'=>'Data Analysis',
            'statistics'=>'Statistics','machine_learning'=>'Machine Learning','research'=>'Research',
            'software'=>'Software Dev','cloud'=>'Cloud','java'=>'Java','javascript'=>'JavaScript','api'=>'API Design',
            'ui_ux'=>'UI/UX','figma'=>'Figma','graphic_design'=>'Graphic Design','design_thinking'=>'Design Thinking',
            'marketing'=>'Marketing','seo'=>'SEO','social_media'=>'Social Media','content'=>'Content','writing'=>'Writing',
            'sales'=>'Sales','customer_service'=>'Customer Service','accounting'=>'Accounting','finance'=>'Finance','audit'=>'Audit',
            'communication'=>'Communication','stakeholder_mgmt'=>'Stakeholder Mgmt','leadership'=>'Leadership',
            'project_mgmt'=>'Project Mgmt','budgeting'=>'Budgeting','teamwork'=>'Teamwork','community'=>'Community',
            'entrepreneurship'=>'Entrepreneurship','innovation'=>'Innovation','teaching'=>'Teaching','excel'=>'Excel',
        ];
        return $m[$c] ?? ucwords(str_replace('_', ' ', $c));
    }

    public static function labels(array $codes): array
    {
        return array_map(fn ($c) => self::label($c), $codes);
    }
}
