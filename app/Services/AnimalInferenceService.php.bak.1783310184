<?php

namespace App\Services;

/**
 * AnimalInferenceService (Fasa 6)
 * Full 12-archetype Work Animal engine (candidate side).
 * Deterministic, explainable, safe-worded ("shows strong X signals" — never "you are permanently X").
 * Output: primary/secondary/growth + confidence + scores + detected evidence + explanation + career fit + growth advice.
 */
class AnimalInferenceService
{
    /** 12 archetypes: id => [label, role, category(L/R/E), traits[], domains[], careerFit[], growth] */
    public static function animals(): array
    {
        return [
            // LEADERSHIP
            'lion'     => ['Lion', 'The Commander', 'L', ['Bold','Decisive','Persuasive'], ['Business'], ['Sales','Business Development','Management Trainee'], 'Balance drive with listening — pair command with empathy.'],
            'eagle'    => ['Eagle', 'The Visionary', 'L', ['Visionary','Strategic','Driven'], ['Business','Engineering'], ['Product','Strategy','Founder / Management Trainee'], 'Ground big ideas with disciplined execution.'],
            'wolf'     => ['Wolf', 'The Pack Leader', 'L', ['Coordinating','Loyal','Team-driven'], ['Business','Engineering'], ['Operations','Project Lead','Logistics'], 'Develop independent decision-making under ambiguity.'],
            'owl'      => ['Owl', 'The Scholar', 'L', ['Analytical','Independent','Precise'], ['Data','Engineering'], ['Data Analyst','Auditor','Researcher'], 'Practise communicating insight to non-experts.'],
            // RELATIONAL
            'dolphin'  => ['Dolphin', 'The Connector', 'R', ['Collaborative','Empathetic','Communicative'], ['Business','Design'], ['Customer Success','HR','Community'], 'Add a measurable/technical skill to deepen impact.'],
            'peacock'  => ['Peacock', 'The Performer', 'R', ['Expressive','Persuasive','Creative'], ['Business','Design'], ['Marketing','Brand','Content'], 'Back your ideas with data and follow-through.'],
            'elephant' => ['Elephant', 'The Mentor', 'R', ['Patient','Supportive','Wise'], ['Business','Design'], ['Training','HR','Education'], 'Step into leading change, not only guiding it.'],
            'horse'    => ['Horse', 'The Loyalist', 'R', ['Reliable','Steady','Dependable'], ['Business','Engineering'], ['Operations','Support','Field Service'], 'Practise navigating tasks without fixed rules.'],
            // EXECUTION
            'ant'      => ['Ant', 'The Architect', 'E', ['Methodical','Reliable','Detailed'], ['Engineering','Data'], ['Engineer','Backend Dev','QA'], 'Zoom out to the big picture more often.'],
            'cheetah'  => ['Cheetah', 'The Sprinter', 'E', ['Fast','Agile','Competitive'], ['Data','Business'], ['Startup','Growth','Sales'], 'Add depth and documentation to your speed.'],
            'fox'      => ['Fox', 'The Strategist', 'E', ['Adaptable','Resourceful','Quick'], ['Business','Data'], ['Product Analyst','Business Analyst','Strategy'], 'Commit to finishing what you start.'],
            'octopus'  => ['Octopus', 'The Maker', 'E', ['Versatile','Creative','Hands-on'], ['Design','Engineering'], ['UX/Product Design','Full-stack','Multimedia'], 'Sharpen one specialism to complement your range.'],
        ];
    }

    private const CATEGORY = ['L' => 'Leadership', 'R' => 'Relational', 'E' => 'Execution'];

    /** Keyword + skill-code signals per animal. */
    private static function signals(): array
    {
        return [
            'lion'     => ['kw'=>['president','captain',' led','head ','manage','director','chair','treasurer','sales','negotiat','win '], 'sk'=>['leadership','stakeholder_mgmt','sales','budgeting']],
            'eagle'    => ['kw'=>['found','startup','vision','strategy','innovat','launch','entrepreneur','pitch'], 'sk'=>['entrepreneurship','innovation']],
            'wolf'     => ['kw'=>['coordinat','operations','cross-functional','logistics','supply','pack','team lead','rotation'], 'sk'=>['project_mgmt']],
            'owl'      => ['kw'=>['analy','data','research','statistic',' sql','python','dashboard','audit','detail','report'], 'sk'=>['data_analysis','sql','statistics','machine_learning','research','accounting','finance','dashboarding']],
            'dolphin'  => ['kw'=>['volunteer','community','help','customer','communicat','collaborat','service','charity'], 'sk'=>['communication','customer_service','community','teamwork']],
            'peacock'  => ['kw'=>['market','brand','content','social media','campaign','public speaking','present','creative','host','perform'], 'sk'=>['marketing','social_media','content','seo']],
            'elephant' => ['kw'=>['mentor','teach','tutor','coach','guide','train','facilitat'], 'sk'=>['teaching']],
            'horse'    => ['kw'=>['reliable','consistent','support','maintain','assist','loyal','steady','follow','routine'], 'sk'=>[]],
            'ant'      => ['kw'=>['built','build','system','process','backend','organis','structure','method','engineer','cad','maintenance','assembl'], 'sk'=>['software','cloud','api']],
            'cheetah'  => ['kw'=>['fast','quick','hackathon','sprint','rapid','deadline','agile','competition','won '], 'sk'=>[]],
            'fox'      => ['kw'=>['strategy','adapt','business','product','resourceful','growth','pivot'], 'sk'=>['marketing','sales','data_analysis']],
            'octopus'  => ['kw'=>['design','prototype',' ui','ux','figma','multimedia','maker','versatile','wireframe'], 'sk'=>['ui_ux','figma','graphic_design','design_thinking','javascript']],
        ];
    }

    /**
     * Infer archetypes from inferred skills (code=>meta) + free-text evidence.
     * Returns a structure compatible with the old animalFromEvidence() plus richer fields.
     */
    public function infer(array $skills, string $text): array
    {
        $t = ' ' . strtolower($text) . ' ';
        $codes = array_keys($skills);
        $animals = self::animals();
        $sig = self::signals();

        $scores = []; $evidence = [];
        foreach ($animals as $id => $_) {
            $scores[$id] = 0; $evidence[$id] = [];
            foreach ($sig[$id]['kw'] as $k) {
                if (str_contains($t, $k)) { $scores[$id] += 1; $evidence[$id][] = trim($k); }
            }
            foreach ($sig[$id]['sk'] as $c) {
                if (in_array($c, $codes, true)) { $scores[$id] += 1; }
            }
        }

        arsort($scores);
        $order = array_keys($scores);
        $total = max(1, array_sum($scores));
        $primaryId   = $order[0];
        $secondaryId = $order[1] ?? 'fox';

        // growth = highest-scoring animal in a DIFFERENT category than primary (complementary edge)
        $primaryCat = $animals[$primaryId][2];
        $growthId = null;
        foreach ($order as $id) {
            if ($id === $primaryId || $id === $secondaryId) continue;
            if ($animals[$id][2] !== $primaryCat) { $growthId = $id; break; }
        }
        $growthId = $growthId ?? end($order);

        $conf = (int) round(100 * $scores[$primaryId] / $total);
        $conf = max(45, min(92, $conf ?: 55));

        $pack = fn ($id) => [
            'id' => $id, 'label' => $animals[$id][0], 'role' => $animals[$id][1],
            'category' => self::CATEGORY[$animals[$id][2]], 'traits' => $animals[$id][3],
            'domains' => $animals[$id][4], 'careerFit' => $animals[$id][5],
        ];

        $pl = $animals[$primaryId][0]; $sl = $animals[$secondaryId][0];
        $ev = array_slice(array_unique($evidence[$primaryId] ?? []), 0, 4);
        $line = "Your resume shows strong {$pl} signals ({$animals[$primaryId][1]}), with a secondary {$sl} streak. "
              . 'This reflects how you work now — not a fixed label.';
        $explanation = $ev
            ? 'Detected from evidence such as: ' . implode(', ', $ev) . '.'
            : 'Inferred from the overall shape of your profile.';

        return [
            'primary'    => $pack($primaryId),
            'secondary'  => $pack($secondaryId),
            'growth'     => $pack($growthId),
            'confidence' => $conf,
            'line'       => $line,
            'explanation'=> $explanation,
            'scores'     => $scores,
            'evidence'   => $ev,
            'careerFit'  => $animals[$primaryId][5],
            'growthAdvice' => $animals[$growthId][6],
        ];
    }
}
