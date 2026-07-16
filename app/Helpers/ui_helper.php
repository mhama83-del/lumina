<?php
/**
 * Lumina UI helper (Fasa 0)
 * Load with: helper('ui');
 */

if (! function_exists('lumina_donut')) {
    function lumina_donut(int $pct, string $label = '', string $color = 'var(--indigo)'): string
    {
        $pct = max(0, min(100, $pct));
        $r = 45; $c = 2 * M_PI * $r; $off = $c * (1 - $pct / 100);
        return '
        <div class="donut-wrap">
          <svg class="donut" viewBox="0 0 120 120">
            <circle class="track" cx="60" cy="60" r="' . $r . '"></circle>
            <circle class="val" cx="60" cy="60" r="' . $r . '"
              style="stroke:' . $color . '; stroke-dasharray:' . round($c, 1) . '; stroke-dashoffset:' . round($off, 1) . ';"></circle>
            <text class="pct" x="60" y="68" text-anchor="middle">' . $pct . '%</text>
          </svg>' . ($label ? '<div class="donut-label">' . esc($label) . '</div>' : '') . '
        </div>';
    }
}

if (! function_exists('lumina_chip')) {
    function lumina_chip(string $label, string $variant = 'indigo'): string
    {
        return '<div class="pillar-chip chip-' . esc($variant) . '">' . esc($label) . '</div>';
    }
}

if (! function_exists('lumina_ring')) {
    function lumina_ring(string $num, string $title, string $body, string $color = ''): string
    {
        return '
        <div class="ring-item">
          <div class="ring ' . esc($color) . '">' . esc($num) . '</div>
          <div><h3 style="margin-bottom:2px">' . esc($title) . '</h3><p class="muted">' . esc($body) . '</p></div>
        </div>';
    }
}

if (! function_exists('lumina_kpi')) {
    function lumina_kpi(string $num, string $label, string $tag = '', string $href = ''): string
    {
        $inner = '
        <div class="card card-tight kpi' . ($href ? ' kpi-link' : '') . '">
          <div class="num">' . esc($num) . '</div>
          <div class="lab">' . esc($label) . '</div>' .
          ($tag ? '<span class="tag">' . esc($tag) . '</span>' : '') . '
        </div>';
        return $href ? '<a href="' . esc($href, 'attr') . '" style="text-decoration:none;color:inherit;display:block">' . $inner . '</a>' : $inner;
    }
}

if (! function_exists('lumina_skill')) {
    function lumina_skill(string $label, string $source = 'stated', float $conf = 1.0): string
    {
        $cls = $source === 'inferred' ? 'skill inferred' : 'skill';
        $c   = $source === 'inferred' ? '<span class="conf">' . round($conf * 100) . '%</span>' : '';
        return '<span class="' . $cls . '">' . esc($label) . ' ' . $c . '</span>';
    }
}

if (! function_exists('lumina_journey')) {
    /** Candidate journey progress bar. $active in: portfolio | compass | match */
    function lumina_journey(string $active): string
    {
        $steps = [['portfolio', 'Build portfolio'], ['compass', 'Find direction'], ['match', 'Match']];
        $seen = false; $html = '<div class="journey">';
        foreach ($steps as $i => [$key, $label]) {
            $isActive = ($key === $active);
            if ($i > 0) $html .= '<div class="jsep"></div>';
            $html .= '<div class="jstep ' . ($isActive ? 'active' : '') . '">'
                   . '<span class="jdot">' . ($i + 1) . '</span><span>' . esc($label) . '</span></div>';
        }
        return $html . '</div>';
    }
}

if (! function_exists('lumina_note')) {
    /** A green "what just happened" caption line. */
    function lumina_note(string $text): string
    {
        return '<div style="display:flex;gap:8px;align-items:center;background:rgba(34,197,94,.08);'
             . 'border:1px solid rgba(34,197,94,.25);color:#4ade80;padding:9px 13px;border-radius:10px;'
             . 'font-size:13px;margin:0 0 16px"><span>✓</span><span>' . $text . '</span></div>';
    }
}
if (! function_exists('lumina_career_journey')) {
    /**
     * Career Action Journey (Strategic C3): Prepare -> Apply -> Perform ->
     * Progress. Reuses the existing .journey/.jstep/.jsep/.jdot CSS classes
     * (same visual language as lumina_journey()) — additive, does not
     * change that function. Non-active steps are clickable links.
     */
    function lumina_career_journey(string $active): string
    {
        $steps = [
            ['prepare',  'Prepare',  base_url('passport')],
            ['apply',    'Apply',    base_url('compass')],
            ['perform',  'Perform',  base_url('match')],
            ['progress', 'Progress', base_url('compass')],
        ];
        $html = '<div class="journey career-journey">';
        foreach ($steps as $i => [$key, $label, $url]) {
            $isActive = ($key === $active);
            if ($i > 0) $html .= '<div class="jsep"></div>';
            $inner = '<span class="jdot">' . ($i + 1) . '</span><span>' . esc($label) . '</span>';
            if ($isActive) {
                $html .= '<div class="jstep active">' . $inner . '</div>';
            } else {
                $html .= '<a href="' . esc($url, 'attr') . '" class="jstep" style="text-decoration:none;color:inherit">' . $inner . '</a>';
            }
        }
        return $html . '</div>';
    }
}
