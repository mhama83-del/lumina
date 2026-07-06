<?php

namespace App\Libraries;

/**
 * Shared roles catalog for Smart Matching + Employer + Resume Analysis.
 *
 * Domain taxonomy (11, grounded in UNESCO ISCED-F 2013 broad fields of
 * education, adapted for Malaysian graduate job-matching):
 *   Data, Engineering, Design, Business (existing, tech/business-oriented)
 *   Education, Arts & Humanities, Social Sciences, Natural Sciences,
 *   Agriculture & Veterinary, Health & Welfare, Services (added — covers the
 *   remaining ISCED-F broad fields: 01 Education, 02 Arts/humanities,
 *   03 Social sciences/journalism, 05 Natural sciences/maths/stats,
 *   08 Agriculture/forestry/fisheries/vet, 09 Health/welfare, 10 Services).
 */
class Catalog
{
    public static function roles(): array
    {
        return [
            // ---- Data ----
            'data_analyst'      => ['key'=>'data_analyst','title'=>'Data Analyst','company'=>'Maybank','location'=>'KL','salary'=>'RM6–8k','domain'=>'Data','color'=>'var(--indigo)','hex'=>'#6C5CE7','required'=>['sql','dashboarding','python','data_analysis']],
            'ml_engineer'       => ['key'=>'ml_engineer','title'=>'ML Engineer','company'=>'Petronas','location'=>'KL','salary'=>'RM9–12k','domain'=>'Data','color'=>'var(--indigo)','hex'=>'#6C5CE7','required'=>['python','machine_learning','statistics','sql']],

            // ---- Engineering ----
            'software_engineer' => ['key'=>'software_engineer','title'=>'Software Engineer','company'=>'Grab','location'=>'KL','salary'=>'RM8–11k','domain'=>'Engineering','color'=>'var(--teal)','hex'=>'#14B8A6','required'=>['software','javascript','api','communication']],
            'backend_engineer'  => ['key'=>'backend_engineer','title'=>'Backend Engineer','company'=>'CIMB','location'=>'KL','salary'=>'RM8–10k','domain'=>'Engineering','color'=>'var(--teal)','hex'=>'#14B8A6','required'=>['software','cloud','java','api']],
            'mechanical_engineer' => ['key'=>'mechanical_engineer','title'=>'Mechanical Engineer','company'=>'PETRONAS','location'=>'KL','salary'=>'RM4–6k','domain'=>'Engineering','color'=>'var(--teal)','hex'=>'#14B8A6','required'=>['mechanical_design','cad','fea','teamwork']],
            'aerospace_engineer' => ['key'=>'aerospace_engineer','title'=>'Aerospace Engineer','company'=>'AirAsia','location'=>'KL','salary'=>'RM5–8k','domain'=>'Engineering','color'=>'var(--teal)','hex'=>'#14B8A6','required'=>['aerodynamics','cad','structural_engineering','teamwork']],
            'electrical_engineer' => ['key'=>'electrical_engineer','title'=>'Electrical Engineer','company'=>'Tenaga Nasional','location'=>'KL','salary'=>'RM4–6k','domain'=>'Engineering','color'=>'var(--teal)','hex'=>'#14B8A6','required'=>['circuit_design','electrical_schematics','plc','teamwork']],
            'civil_engineer'    => ['key'=>'civil_engineer','title'=>'Civil Engineer','company'=>'Gamuda','location'=>'KL','salary'=>'RM4–6k','domain'=>'Engineering','color'=>'var(--teal)','hex'=>'#14B8A6','required'=>['structural_engineering','project_planning','teamwork','communication']],
            'process_engineer'  => ['key'=>'process_engineer','title'=>'Process Engineer','company'=>'PETRONAS','location'=>'KL','salary'=>'RM4–6k','domain'=>'Engineering','color'=>'var(--teal)','hex'=>'#14B8A6','required'=>['chemical_processing','process_safety','project_planning','teamwork']],

            // ---- Design ----
            'ux_designer'       => ['key'=>'ux_designer','title'=>'UX Designer','company'=>'AirAsia','location'=>'KL','salary'=>'RM5–8k','domain'=>'Design','color'=>'var(--violet)','hex'=>'#a78bfa','required'=>['ui_ux','figma','design_thinking','communication']],

            // ---- Business (incl. Law/Admin per ISCED-04) ----
            'marketing_exec'    => ['key'=>'marketing_exec','title'=>'Marketing Executive','company'=>'Nestlé','location'=>'Selangor','salary'=>'RM4–6k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['marketing','social_media','content','communication']],
            'product_exec'      => ['key'=>'product_exec','title'=>'Product Executive','company'=>'Shopee','location'=>'KL','salary'=>'RM6–9k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['stakeholder_mgmt','communication','leadership','project_mgmt']],
            'accountant'        => ['key'=>'accountant','title'=>'Accountant','company'=>'KPMG','location'=>'KL','salary'=>'RM5–7k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['accounting','finance','excel','communication']],
            'sales_exec'        => ['key'=>'sales_exec','title'=>'Sales Executive','company'=>'Maxis','location'=>'KL','salary'=>'RM4–7k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['sales','communication','customer_service']],
            'content_creator'   => ['key'=>'content_creator','title'=>'Content Strategist','company'=>'Astro','location'=>'KL','salary'=>'RM4–6k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['content','social_media','writing','seo']],
            'legal_associate'   => ['key'=>'legal_associate','title'=>'Legal Associate / Paralegal','company'=>'Zul Rafique & Partners','location'=>'KL','salary'=>'RM3.5–5.5k','domain'=>'Business','color'=>'var(--gold)','hex'=>'#FDE047','required'=>['legal_research','writing','attention_to_detail','communication']],

            // ---- Education (ISCED-01) ----
            'teacher'             => ['key'=>'teacher','title'=>'Teacher / Educator','company'=>'Ministry of Education Malaysia','location'=>'KL','salary'=>'RM3–5k','domain'=>'Education','color'=>'#38BDF8','hex'=>'#38BDF8','required'=>['lesson_planning','classroom_mgmt','communication','curriculum_design']],
            'corporate_trainer'   => ['key'=>'corporate_trainer','title'=>'Corporate Trainer / L&D Executive','company'=>'Petronas','location'=>'KL','salary'=>'RM5–8k','domain'=>'Education','color'=>'#38BDF8','hex'=>'#38BDF8','required'=>['training_design','communication','presentation','stakeholder_mgmt']],
            'education_counselor' => ['key'=>'education_counselor','title'=>'Student Counsellor','company'=>'Sunway University','location'=>'Selangor','salary'=>'RM4–6k','domain'=>'Education','color'=>'#38BDF8','hex'=>'#38BDF8','required'=>['counselling','communication','case_mgmt','empathy']],

            // ---- Arts & Humanities (ISCED-02) ----
            'translator'          => ['key'=>'translator','title'=>'Translator / Linguist','company'=>'Bernama','location'=>'KL','salary'=>'RM3–5k','domain'=>'Arts & Humanities','color'=>'#FB7185','hex'=>'#FB7185','required'=>['translation','writing','research','communication']],
            'performing_artist'   => ['key'=>'performing_artist','title'=>'Performing Arts Practitioner','company'=>'Istana Budaya','location'=>'KL','salary'=>'RM2.5–4.5k','domain'=>'Arts & Humanities','color'=>'#FB7185','hex'=>'#FB7185','required'=>['performance','creativity','stage_presence','teamwork']],
            'heritage_researcher' => ['key'=>'heritage_researcher','title'=>'Heritage & Archives Researcher','company'=>'Department of Museums Malaysia','location'=>'KL','salary'=>'RM3–5k','domain'=>'Arts & Humanities','color'=>'#FB7185','hex'=>'#FB7185','required'=>['research','archival_work','writing','attention_to_detail']],

            // ---- Social Sciences (ISCED-03) ----
            'journalist'      => ['key'=>'journalist','title'=>'Journalist / Reporter','company'=>'Bernama','location'=>'KL','salary'=>'RM3–5k','domain'=>'Social Sciences','color'=>'#FB923C','hex'=>'#FB923C','required'=>['writing','research','interviewing','communication']],
            'policy_analyst'  => ['key'=>'policy_analyst','title'=>'Policy Analyst','company'=>'ISIS Malaysia','location'=>'KL','salary'=>'RM4–6k','domain'=>'Social Sciences','color'=>'#FB923C','hex'=>'#FB923C','required'=>['research','policy_analysis','writing','stakeholder_mgmt']],
            'social_worker'   => ['key'=>'social_worker','title'=>'Social Worker','company'=>'Ministry of Women, Family and Community Development','location'=>'Putrajaya','salary'=>'RM3–5k','domain'=>'Social Sciences','color'=>'#FB923C','hex'=>'#FB923C','required'=>['counselling','case_mgmt','empathy','communication']],

            // ---- Natural Sciences (ISCED-05) ----
            'research_scientist' => ['key'=>'research_scientist','title'=>'Research Scientist','company'=>'MOSTI','location'=>'Putrajaya','salary'=>'RM4–7k','domain'=>'Natural Sciences','color'=>'#22C55E','hex'=>'#22C55E','required'=>['research','lab_techniques','statistics','scientific_writing']],
            'lab_analyst'        => ['key'=>'lab_analyst','title'=>'Lab / Quality Analyst','company'=>'Petronas','location'=>'KL','salary'=>'RM3.5–5.5k','domain'=>'Natural Sciences','color'=>'#22C55E','hex'=>'#22C55E','required'=>['lab_techniques','quality_control','data_analysis','attention_to_detail']],
            'actuarial_analyst'  => ['key'=>'actuarial_analyst','title'=>'Actuarial Analyst','company'=>'Great Eastern','location'=>'KL','salary'=>'RM5–8k','domain'=>'Natural Sciences','color'=>'#22C55E','hex'=>'#22C55E','required'=>['statistics','mathematics','risk_modeling','excel']],

            // ---- Agriculture & Veterinary (ISCED-08) ----
            'agronomist'           => ['key'=>'agronomist','title'=>'Agronomist / Agriculture Officer','company'=>'FELDA','location'=>'Selangor','salary'=>'RM3–5k','domain'=>'Agriculture & Veterinary','color'=>'#84CC16','hex'=>'#84CC16','required'=>['crop_science','field_research','sustainability','data_analysis']],
            'veterinary_assistant' => ['key'=>'veterinary_assistant','title'=>'Veterinary Assistant','company'=>'Department of Veterinary Services Malaysia','location'=>'Putrajaya','salary'=>'RM3–4.5k','domain'=>'Agriculture & Veterinary','color'=>'#84CC16','hex'=>'#84CC16','required'=>['animal_care','clinical_skills','communication','attention_to_detail']],
            'aquaculture_officer'  => ['key'=>'aquaculture_officer','title'=>'Aquaculture / Fisheries Officer','company'=>'LKIM','location'=>'Terengganu','salary'=>'RM3–4.5k','domain'=>'Agriculture & Veterinary','color'=>'#84CC16','hex'=>'#84CC16','required'=>['aquaculture','field_research','sustainability','data_analysis']],

            // ---- Health & Welfare (ISCED-09) ----
            'clinical_officer'      => ['key'=>'clinical_officer','title'=>'Clinical / Healthcare Officer','company'=>'KPJ Healthcare','location'=>'KL','salary'=>'RM3.5–5.5k','domain'=>'Health & Welfare','color'=>'#EF4444','hex'=>'#EF4444','required'=>['clinical_skills','patient_care','communication','attention_to_detail']],
            'pharmacist_assistant'  => ['key'=>'pharmacist_assistant','title'=>'Pharmacy Assistant / Officer','company'=>'Guardian Malaysia','location'=>'KL','salary'=>'RM3–4.5k','domain'=>'Health & Welfare','color'=>'#EF4444','hex'=>'#EF4444','required'=>['pharmacology','patient_care','attention_to_detail','communication']],
            'physiotherapist'       => ['key'=>'physiotherapist','title'=>'Physiotherapist','company'=>'Gleneagles Hospital','location'=>'KL','salary'=>'RM4–6k','domain'=>'Health & Welfare','color'=>'#EF4444','hex'=>'#EF4444','required'=>['clinical_skills','patient_care','rehabilitation','communication']],
            'public_health_officer' => ['key'=>'public_health_officer','title'=>'Public Health Officer','company'=>'Ministry of Health Malaysia','location'=>'Putrajaya','salary'=>'RM3.5–5.5k','domain'=>'Health & Welfare','color'=>'#EF4444','hex'=>'#EF4444','required'=>['public_health','data_analysis','communication','community_outreach']],

            // ---- Services (ISCED-10) ----
            'hospitality_exec' => ['key'=>'hospitality_exec','title'=>'Hospitality / Guest Relations Executive','company'=>'Shangri-La Hotels','location'=>'KL','salary'=>'RM3–4.5k','domain'=>'Services','color'=>'#A855F7','hex'=>'#A855F7','required'=>['customer_service','communication','hospitality_ops','teamwork']],
            'tourism_officer'  => ['key'=>'tourism_officer','title'=>'Tourism & Travel Officer','company'=>'Tourism Malaysia','location'=>'KL','salary'=>'RM3–4.5k','domain'=>'Services','color'=>'#A855F7','hex'=>'#A855F7','required'=>['customer_service','event_planning','communication','marketing']],
            'logistics_exec'   => ['key'=>'logistics_exec','title'=>'Logistics / Supply Chain Executive','company'=>'DHL Malaysia','location'=>'Selangor','salary'=>'RM3.5–5.5k','domain'=>'Services','color'=>'#A855F7','hex'=>'#A855F7','required'=>['logistics','project_planning','communication','data_analysis']],
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
            'mechanical_design'=>'Mechanical Design','cad'=>'CAD','fea'=>'FEA','structural_engineering'=>'Structural Engineering',
            'manufacturing'=>'Manufacturing','six_sigma'=>'Six Sigma',
            'aerodynamics'=>'Aerodynamics','aircraft_systems'=>'Aircraft Systems','avionics'=>'Avionics',
            'circuit_design'=>'Circuit Design','plc'=>'PLC','electrical_schematics'=>'Electrical Schematics',
            'embedded'=>'Embedded Systems','robotics'=>'Robotics','control_systems'=>'Control Systems',
            'process_safety'=>'Process Safety','chemical_processing'=>'Chemical Processing','project_planning'=>'Project Planning',
            // ---- new (11-domain expansion) ----
            'legal_research'=>'Legal Research','attention_to_detail'=>'Attention to Detail','presentation'=>'Presentation',
            'lesson_planning'=>'Lesson Planning','classroom_mgmt'=>'Classroom Management','curriculum_design'=>'Curriculum Design','training_design'=>'Training Design',
            'counselling'=>'Counselling','case_mgmt'=>'Case Management','empathy'=>'Empathy',
            'translation'=>'Translation','performance'=>'Performance','creativity'=>'Creativity','stage_presence'=>'Stage Presence',
            'archival_work'=>'Archival Work',
            'interviewing'=>'Interviewing','policy_analysis'=>'Policy Analysis',
            'lab_techniques'=>'Lab Techniques','scientific_writing'=>'Scientific Writing','quality_control'=>'Quality Control',
            'risk_modeling'=>'Risk Modelling','mathematics'=>'Mathematics',
            'crop_science'=>'Crop Science','field_research'=>'Field Research','sustainability'=>'Sustainability',
            'animal_care'=>'Animal Care','aquaculture'=>'Aquaculture',
            'clinical_skills'=>'Clinical Skills','patient_care'=>'Patient Care','pharmacology'=>'Pharmacology',
            'rehabilitation'=>'Rehabilitation','public_health'=>'Public Health','community_outreach'=>'Community Outreach',
            'hospitality_ops'=>'Hospitality Operations','event_planning'=>'Event Planning','logistics'=>'Logistics',
        ];
        return $m[$c] ?? ucwords(str_replace('_', ' ', $c));
    }

    public static function labels(array $codes): array
    {
        return array_map(fn ($c) => self::label($c), $codes);
    }
}
