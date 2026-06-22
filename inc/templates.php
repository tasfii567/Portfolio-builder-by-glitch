<?php
/**
 * inc/templates.php — the 5 portfolio templates.
 * One source of truth used by templates.php, create-portfolio.php and view-portfolio.php.
 */
function portfolio_templates(): array {
    return [
        'midnight' => ['name' => 'Midnight', 'desc' => 'Classic dark hero with a project grid.',   'c1' => '#241b4a', 'c2' => '#5a2f4d', 'dot' => '#8b7cf0'],
        'aurora'   => ['name' => 'Aurora',   'desc' => 'Split layout — profile left, work right.',  'c1' => '#08332e', 'c2' => '#0f5d54', 'dot' => '#2ecc71'],
        'sunset'   => ['name' => 'Sunset',   'desc' => 'Bold magazine cover in warm tones.',         'c1' => '#4a1f23', 'c2' => '#7a3a2f', 'dot' => '#ff7a59'],
        'minimal'  => ['name' => 'Minimal',  'desc' => 'Clean light theme, centered single column.', 'c1' => '#e9ecf5', 'c2' => '#cfd6e6', 'dot' => '#6c5ce7'],
        'terminal' => ['name' => 'Terminal', 'desc' => 'Developer / code theme in monospace.',       'c1' => '#0b1a12', 'c2' => '#12351f', 'dot' => '#39d353'],
    ];
}
function valid_template(string $key): string {
    $t = portfolio_templates();
    return isset($t[$key]) ? $key : 'midnight';
}
