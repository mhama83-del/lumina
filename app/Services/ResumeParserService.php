<?php

namespace App\Services;

use App\Libraries\WorkAnimal;

/**
 * ResumeParserService (Fasa 2)
 * Membina "extracted profile" yang lebih kaya di atas ScoreService:
 * projects, leadership, career cluster, field of study, dan Work Animal
 * (interim 6-animal; Fasa 6 naik taraf ke AnimalInferenceService 12-animal).
 * Deterministik & explainable — tiada panggilan AI luar.
 */
class ResumeParserService
{
    /**
     * role key => skills typically GAINED through that programme (core /
     * taught competencies), independent of whatever the candidate happened
     * to write in their activities/projects text. Used to ground the
     * INITIAL job-description matching once a field of study is detected —
     * softskills/activities (leadership, teamwork, communication, etc, from
     * ScoreService's keyword map) only reinforce the resume afterwards, they
     * do not drive which role family is selected.
     */
    private const CORE_SKILLS = [
        'process_engineer'    => ['chemical_processing', 'process_safety', 'project_planning'],
        'mechanical_engineer' => ['mechanical_design', 'cad', 'fea'],
        'aerospace_engineer'  => ['aerodynamics', 'structural_engineering', 'cad'],
        'electrical_engineer' => ['circuit_design', 'electrical_schematics', 'plc'],
        'civil_engineer'      => ['structural_engineering', 'project_planning'],
        'software_engineer'   => ['software', 'api', 'cloud'],
        'data_analyst'        => ['sql', 'data_analysis', 'statistics'],
        'ml_engineer'         => ['python', 'machine_learning', 'statistics'],
        'ux_designer'         => ['ui_ux', 'design_thinking'],
        'accountant'          => ['accounting', 'finance', 'excel'],
        'marketing_exec'      => ['marketing', 'content'],
        'product_exec'        => ['stakeholder_mgmt', 'project_mgmt'],
        'content_creator'     => ['content', 'writing'],
        'legal_associate'     => ['legal_research', 'writing'],
        'teacher'             => ['lesson_planning', 'classroom_mgmt', 'curriculum_design'],
        'corporate_trainer'   => ['training_design', 'presentation'],
        'education_counselor' => ['counselling', 'case_mgmt'],
        'translator'          => ['translation', 'writing'],
        'performing_artist'   => ['performance', 'creativity'],
        'heritage_researcher' => ['research', 'archival_work'],
        'journalist'          => ['writing', 'interviewing'],
        'policy_analyst'      => ['research', 'policy_analysis'],
        'social_worker'       => ['counselling', 'case_mgmt'],
        'research_scientist'  => ['research', 'lab_techniques', 'scientific_writing'],
        'lab_analyst'         => ['lab_techniques', 'quality_control'],
        'actuarial_analyst'   => ['statistics', 'mathematics', 'risk_modeling'],
        'agronomist'          => ['crop_science', 'field_research', 'sustainability'],
        'veterinary_assistant'=> ['animal_care', 'clinical_skills'],
        'aquaculture_officer' => ['aquaculture', 'field_research'],
        'clinical_officer'    => ['clinical_skills', 'patient_care'],
        'pharmacist_assistant'=> ['pharmacology', 'patient_care'],
        'physiotherapist'     => ['clinical_skills', 'rehabilitation'],
        'public_health_officer'=> ['public_health', 'community_outreach'],
        'hospitality_exec'    => ['hospitality_ops', 'customer_service'],
        'tourism_officer'     => ['event_planning', 'customer_service'],
        'logistics_exec'      => ['logistics', 'project_planning'],
    ];

    /** Klausa yang menandakan projek. */
    public function detectProjects(string $text): array
    {
        return $this->pick($text, ['project','app','built','build','developed','system','website','dashboard','prototype','platform']);
    }

    /** Klausa yang menandakan kepimpinan / aktiviti pimpinan. */
    public function detectLeadership(string $text): array
    {
        return $this->pick($text, ['treasurer','president','captain','head ','led ','lead ','chair','director','manage','mentor','coordinat','organis','founder']);
    }

    /** Pecah teks jadi klausa; pulang yang mengandungi mana-mana kata kunci. */
    private function pick(string $text, array $kw): array
    {
        $clauses = array_filter(array_map('trim', preg_split('/[;.\n]+/', $text)));
        $out = [];
        foreach ($clauses as $c) {
            $lc = strtolower($c);
            foreach ($kw as $k) {
                if (str_contains($lc, $k)) { $out[] = $this->tidy($c); break; }
            }
        }
        return array_values(array_unique(array_slice($out, 0, 6)));
    }

    private function tidy(string $s): string
    {
        $s = trim($s);
        return mb_strlen($s) > 120 ? mb_substr($s, 0, 117) . '…' : $s;
    }

    /** Career cluster daripada domain. */
    public function careerCluster(string $domain): string
    {
        return [
            'Data'                     => 'Data, Analytics & AI',
            'Engineering'              => 'Engineering & Applied Sciences',
            'Design'                   => 'Product & Experience Design',
            'Business'                 => 'Business, Growth & Operations',
            'Education'                => 'Education & Training',
            'Arts & Humanities'        => 'Arts, Culture & Humanities',
            'Social Sciences'         => 'Social Sciences & Public Affairs',
            'Natural Sciences'        => 'Natural Sciences & Research',
            'Agriculture & Veterinary' => 'Agriculture & Veterinary Sciences',
            'Health & Welfare'        => 'Health & Welfare',
            'Services'                 => 'Hospitality, Tourism & Services',
        ][$domain] ?? 'Business, Growth & Operations';
    }

    /**
     * Deterministic "field of study" detector.
     *
     * Scans the WHOLE resume text (not one exact sentence layout — layouts
     * vary too much to rely on regex line-anchors) for a known degree /
     * programme phrase, English or Malay. This is what the candidate
     * actually STUDIED — a stronger, more trustworthy signal than generic
     * soft-skill words (communication, teamwork, ...) that show up in
     * almost every resume regardless of field.
     *
     * Covers all 11 Lumina domains (grounded in UNESCO ISCED-F 2013 broad
     * fields of education). Deliberately AVOIDS bare generic words that
     * collide with common resume boilerplate (e.g. "education" as a section
     * header, "history" in "work history", "literature" in "literature
     * review", "law" as a substring of "Malawi"/"outlaw") — every key here
     * is a specific-enough phrase to be a genuine field-of-study signal.
     *
     * Returns:
     *   raw        - human-readable field label (or null if nothing found)
     *   domain     - one of the 11 Lumina domains (or null)
     *   role       - a specific Catalog role key this programme feeds into (or null)
     *   coreSkills - skill codes typically GAINED through this programme —
     *                used to ground initial job-description matching.
     */
    public function extractFieldOfStudy(string $text): array
    {
        $map = [
            // ---- Engineering (specific professional programmes, checked first) ----
            'computer science'         => ['Engineering', 'software_engineer'],
            'sains komputer'           => ['Engineering', 'software_engineer'],
            'computer engineering'     => ['Engineering', 'software_engineer'],
            'software engineering'     => ['Engineering', 'software_engineer'],
            'information technology'   => ['Data', 'data_analyst'],
            'information system'      => ['Data', 'data_analyst'],
            'data science'              => ['Data', 'ml_engineer'],
            'chemical engineering'     => ['Engineering', 'process_engineer'],
            'kejuruteraan kimia'       => ['Engineering', 'process_engineer'],
            'petrochemical'            => ['Engineering', 'process_engineer'],
            'process engineering'      => ['Engineering', 'process_engineer'],
            'mechatronic'               => ['Engineering', 'mechanical_engineer'],
            'mechanical engineering'   => ['Engineering', 'mechanical_engineer'],
            'kejuruteraan mekanikal'   => ['Engineering', 'mechanical_engineer'],
            'aerospace engineering'    => ['Engineering', 'aerospace_engineer'],
            'aeronautical engineering' => ['Engineering', 'aerospace_engineer'],
            'aeronautics'               => ['Engineering', 'aerospace_engineer'],
            'electrical engineering'   => ['Engineering', 'electrical_engineer'],
            'electronic engineering'   => ['Engineering', 'electrical_engineer'],
            'kejuruteraan elektrik'    => ['Engineering', 'electrical_engineer'],
            'civil engineering'         => ['Engineering', 'civil_engineer'],
            'kejuruteraan awam'        => ['Engineering', 'civil_engineer'],
            'structural engineering'   => ['Engineering', 'civil_engineer'],
            'robotics engineering'     => ['Engineering', 'mechanical_engineer'],

            // ---- Business, Administration & Law (ISCED-04) ----
            'business administration'  => ['Business', 'product_exec'],
            'business management'      => ['Business', 'product_exec'],
            'pentadbiran perniagaan'   => ['Business', 'product_exec'],
            'accounting'                => ['Business', 'accountant'],
            'finance'                   => ['Business', 'accountant'],
            'economics'                 => ['Business', 'accountant'],
            'marketing'                 => ['Business', 'marketing_exec'],
            'llb'                       => ['Business', 'legal_associate'],
            'bachelor of laws'          => ['Business', 'legal_associate'],
            'legal studies'             => ['Business', 'legal_associate'],
            'shariah'                   => ['Business', 'legal_associate'],
            'syariah'                   => ['Business', 'legal_associate'],

            // ---- Design (narrow field within Arts, kept distinct — practical UX/product track) ----
            'graphic design'            => ['Design', 'ux_designer'],
            'multimedia'                => ['Design', 'ux_designer'],
            'industrial design'         => ['Design', 'ux_designer'],
            'ux design'                 => ['Design', 'ux_designer'],
            'product design'            => ['Design', 'ux_designer'],

            // ---- Education (ISCED-01) ----
            'bachelor of education'     => ['Education', 'teacher'],
            'b.ed'                      => ['Education', 'teacher'],
            'tesl'                      => ['Education', 'teacher'],
            'teaching english'         => ['Education', 'teacher'],
            'early childhood education' => ['Education', 'teacher'],
            'special education'        => ['Education', 'teacher'],
            'educational psychology'   => ['Education', 'education_counselor'],
            'pendidikan awal kanak-kanak' => ['Education', 'teacher'],

            // ---- Arts & Humanities (ISCED-02, excluding Design) ----
            'performing arts'           => ['Arts & Humanities', 'performing_artist'],
            'fine arts'                 => ['Arts & Humanities', 'performing_artist'],
            'visual arts'               => ['Arts & Humanities', 'performing_artist'],
            'theatre studies'          => ['Arts & Humanities', 'performing_artist'],
            'english literature'      => ['Arts & Humanities', 'translator'],
            'malay literature'         => ['Arts & Humanities', 'translator'],
            'comparative literature'  => ['Arts & Humanities', 'translator'],
            'linguistics'               => ['Arts & Humanities', 'translator'],
            'anthropology'              => ['Arts & Humanities', 'heritage_researcher'],
            'archaeology'               => ['Arts & Humanities', 'heritage_researcher'],
            'religious studies'        => ['Arts & Humanities', 'heritage_researcher'],
            'usuluddin'                 => ['Arts & Humanities', 'heritage_researcher'],

            // ---- Social Sciences, Journalism & Information (ISCED-03) ----
            'mass communication'       => ['Social Sciences', 'journalist'],
            'journalism'                => ['Social Sciences', 'journalist'],
            'political science'        => ['Social Sciences', 'policy_analyst'],
            'international relations'  => ['Social Sciences', 'policy_analyst'],
            'public administration'    => ['Social Sciences', 'policy_analyst'],
            'criminology'               => ['Social Sciences', 'policy_analyst'],
            'gender studies'            => ['Social Sciences', 'policy_analyst'],
            'sociology'                 => ['Social Sciences', 'social_worker'],
            'social work'               => ['Social Sciences', 'social_worker'],
            'educational counselling'  => ['Social Sciences', 'social_worker'],

            // ---- Agriculture, Forestry, Fisheries & Veterinary (ISCED-08) ----
            'agricultural science'     => ['Agriculture & Veterinary', 'agronomist'],
            'agribusiness'              => ['Agriculture & Veterinary', 'agronomist'],
            'agrotechnology'            => ['Agriculture & Veterinary', 'agronomist'],
            'forestry'                  => ['Agriculture & Veterinary', 'agronomist'],
            'veterinary medicine'      => ['Agriculture & Veterinary', 'veterinary_assistant'],
            'veterinary science'       => ['Agriculture & Veterinary', 'veterinary_assistant'],
            'aquaculture'               => ['Agriculture & Veterinary', 'aquaculture_officer'],
            'fisheries science'        => ['Agriculture & Veterinary', 'aquaculture_officer'],

            // ---- Health & Welfare (ISCED-09) ----
            'nutrition and dietetics'  => ['Health & Welfare', 'public_health_officer'],
            'dietetics'                 => ['Health & Welfare', 'public_health_officer'],
            'public health'             => ['Health & Welfare', 'public_health_officer'],
            'medical science'           => ['Health & Welfare', 'clinical_officer'],
            'biomedical science'       => ['Health & Welfare', 'clinical_officer'],
            'nursing'                   => ['Health & Welfare', 'clinical_officer'],
            'dentistry'                 => ['Health & Welfare', 'clinical_officer'],
            'optometry'                 => ['Health & Welfare', 'clinical_officer'],
            'radiography'               => ['Health & Welfare', 'clinical_officer'],
            'midwifery'                 => ['Health & Welfare', 'clinical_officer'],
            'paramedic science'        => ['Health & Welfare', 'clinical_officer'],
            'pharmacy'                  => ['Health & Welfare', 'pharmacist_assistant'],
            'physiotherapy'             => ['Health & Welfare', 'physiotherapist'],
            'occupational therapy'    => ['Health & Welfare', 'physiotherapist'],
            'medicine'                  => ['Health & Welfare', 'clinical_officer'],

            // ---- Services (ISCED-10) ----
            'hospitality management'  => ['Services', 'hospitality_exec'],
            'culinary arts'             => ['Services', 'hospitality_exec'],
            'tourism management'       => ['Services', 'tourism_officer'],
            'aviation management'      => ['Services', 'tourism_officer'],
            'logistics management'    => ['Services', 'logistics_exec'],
            'supply chain management' => ['Services', 'logistics_exec'],

            // ---- Natural Sciences, Mathematics & Statistics (ISCED-05, lower priority — generic single-subject words checked last) ----
            'actuarial science'         => ['Natural Sciences', 'actuarial_analyst'],
            'biotechnology'             => ['Natural Sciences', 'research_scientist'],
            'molecular biology'        => ['Natural Sciences', 'research_scientist'],
            'microbiology'              => ['Natural Sciences', 'lab_analyst'],
            'biochemistry'              => ['Natural Sciences', 'lab_analyst'],
            'environmental science'    => ['Natural Sciences', 'research_scientist'],
            'applied science'           => ['Natural Sciences', 'research_scientist'],
            'statistics'                 => ['Data', 'data_analyst'],
            'mathematics'                => ['Data', 'data_analyst'],
            'physics'                    => ['Natural Sciences', 'research_scientist'],
            'chemistry'                  => ['Natural Sciences', 'lab_analyst'],
            'biology'                    => ['Natural Sciences', 'research_scientist'],
            'psychology'                 => ['Social Sciences', 'social_worker'],
            'counselling'                => ['Social Sciences', 'social_worker'],
            'counseling'                 => ['Social Sciences', 'social_worker'],
            'philosophy'                 => ['Arts & Humanities', 'heritage_researcher'],
        ];

        foreach ($map as $kw => $pair) {
            if (stripos($text, $kw) !== false) {
                [$domain, $role] = $pair;
                return [
                    'raw'        => ucwords($kw),
                    'domain'     => $domain,
                    'role'       => $role,
                    'coreSkills' => self::CORE_SKILLS[$role] ?? [],
                ];
            }
        }
        return ['raw' => null, 'domain' => null, 'role' => null, 'coreSkills' => []];
    }

    /**
     * Work Animal (Fasa 6) — delegates to the full 12-archetype engine.
     * Kept here for backward compatibility with existing callers.
     */
    public function animalFromEvidence(array $skills, string $text): array
    {
        return (new \App\Services\AnimalInferenceService())->infer($skills, $text);
    }
}
