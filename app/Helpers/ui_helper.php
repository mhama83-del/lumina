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
    function lumina_kpi(string $num, string $label, string $tag = ''): string
    {
        return '
        <div class="card card-tight kpi">
          <div class="num">' . esc($num) . '</div>
          <div class="lab">' . esc($label) . '</div>' .
          ($tag ? '<span class="tag">' . esc($tag) . '</span>' : '') . '
        </div>';
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
