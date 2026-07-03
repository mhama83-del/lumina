<?php

namespace App\Libraries;

/**
 * Shared roles catalog for Smart Matching (candidate) + Employer dashboard.
 */
class Catalog
{
    public static function roles(): array
    {
        return [
            'data_analyst'      => ['key' => 'data_analyst',      'title' => 'Data Analyst',      'company' => 'Maybank',  'location' => 'KL',       'salary' => 'RM6–8k',  'domain' => 'Data',        'color' => 'var(--indigo)', 'hex' => '#6D5DFB', 'required' => ['sql', 'dashboarding', 'python', 'data_analysis']],
            'backend_engineer'  => ['key' => 'backend_engineer',  'title' => 'Backend Engineer',  'company' => 'CIMB',     'location' => 'KL',       'salary' => 'RM8–10k', 'domain' => 'Engineering', 'color' => 'var(--teal)',   'hex' => '#14B8A6', 'required' => ['software', 'cloud', 'python', 'communication']],
            'product_exec'      => ['key' => 'product_exec',      'title' => 'Product Executive', 'company' => 'Grab',     'location' => 'KL',       'salary' => 'RM5–7k',  'domain' => 'Business',    'color' => 'var(--gold)',   'hex' => '#F5C518', 'required' => ['stakeholder_mgmt', 'communication', 'leadership']],
            'data_engineer'     => ['key' => 'data_engineer',     'title' => 'Data Engineer',     'company' => 'Petronas', 'location' => 'KL',       'salary' => 'RM9–11k', 'domain' => 'Data',        'color' => 'var(--indigo)', 'hex' => '#6D5DFB', 'required' => ['sql', 'python', 'cloud']],
            'marketing_analyst' => ['key' => 'marketing_analyst', 'title' => 'Marketing Analyst', 'company' => 'Nestlé',   'location' => 'Selangor', 'salary' => 'RM5–7k',  'domain' => 'Business',    'color' => 'var(--gold)',   'hex' => '#F5C518', 'required' => ['communication', 'data_analysis', 'excel']],
        ];
    }

    public static function role(string $key): array
    {
        $r = self::roles();
        return $r[$key] ?? array_values($r)[0];
    }

    public static function label(string $c): string
    {
        $m = ['sql' => 'SQL', 'dashboarding' => 'Dashboarding', 'python' => 'Python', 'data_analysis' => 'Data Analysis',
              'software' => 'Software Dev', 'cloud' => 'Cloud', 'communication' => 'Communication', 'excel' => 'Excel',
              'stakeholder_mgmt' => 'Stakeholder Mgmt', 'leadership' => 'Leadership', 'budgeting' => 'Budgeting',
              'teamwork' => 'Teamwork', 'community' => 'Community', 'design_thinking' => 'Design Thinking', 'entrepreneurship' => 'Entrepreneurship'];
        return $m[$c] ?? ucwords(str_replace('_', ' ', $c));
    }

    public static function labels(array $codes): array
    {
        return array_map(fn ($c) => self::label($c), $codes);
    }
}
