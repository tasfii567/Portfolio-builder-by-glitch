<?php
/**
 * inc/templates.php — registry of PUBLIC portfolio templates.
 *
 * These are the looks used by the shareable page (view-portfolio.php) and are
 * separate from the admin preview renderers in /templates/render-temp*.php.
 * Each key maps to a file /templates/<key>.php.
 */

function portfolio_templates(): array
{
    return [
        'midnight' => ['name' => 'Midnight', 'desc' => 'Dark, modern, gradient accents'],
        'aurora'   => ['name' => 'Aurora',   'desc' => 'Light, clean, colourful'],
        'classic'  => ['name' => 'Classic',  'desc' => 'Minimal, professional, neutral'],
    ];
}

/**
 * Return a known template key, falling back to 'midnight' for anything invalid.
 */
function valid_template(?string $key): string
{
    $key = strtolower(trim((string) $key));
    $all = portfolio_templates();
    return isset($all[$key]) ? $key : 'midnight';
}
