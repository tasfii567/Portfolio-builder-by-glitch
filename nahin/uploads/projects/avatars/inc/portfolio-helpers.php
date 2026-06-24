<?php
/**
 * inc/portfolio-helpers.php — small helpers for the portfolio pages.
 */

/**
 * Build the absolute, shareable URL to the public portfolio page for a slug.
 * Works regardless of which sub-folder the app is served from, because it is
 * derived from the directory of the currently-running script.
 */
function build_share_url(string $slug): string
{
    $https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
          || (($_SERVER['SERVER_PORT'] ?? '') == 443)
          || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Directory the current page lives in, e.g. "/nahin".
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = rtrim($dir, '/');

    return $scheme . '://' . $host . $dir . '/view-portfolio.php?u=' . rawurlencode($slug);
}
