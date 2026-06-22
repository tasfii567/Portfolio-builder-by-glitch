<?php
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include 'resume_data.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Render a <ul> from an array of strings.
 * Uses padding-left on the <ul> (not margin) so Dompdf renders bullet markers
 * inside the content area — Dompdf ignores list-style-position:outside
 * when padding is 0, causing markers to be clipped or hidden.
 */
function bulletList(array $items, string $liStyle = ''): string {
    $items = array_values(array_filter($items, fn($v) => trim((string)$v) !== ''));
    if (empty($items)) {
        return '';
    }
    $li = '';
    foreach ($items as $item) {
        $li .= "<li style='margin-bottom:3px;font-size:11.5px;line-height:1.5;{$liStyle}'>"
             . htmlspecialchars($item) . "</li>";
    }
    // padding-left:18px keeps markers visible in Dompdf
    return "<ul style='margin:4px 0 4px 0;padding-left:18px;'>{$li}</ul>";
}

/**
 * Render one education cell.
 */
function eduCell(array $edu): string {
    $degreeDur = array_filter([$edu['degree'] ?? '', $edu['duration'] ?? ''], fn($v) => $v !== '');
    $instScore = array_filter([$edu['institution'] ?? '', $edu['score'] ?? ''], fn($v) => $v !== '');

    $html = '';
    if (!empty($degreeDur)) {
        $html .= "<div style='font-size:11.5px;'>" . htmlspecialchars(implode(' | ', $degreeDur)) . "</div>";
    }
    if (!empty($instScore)) {
        $html .= "<div style='font-size:11.5px;margin-top:2px;'>" . htmlspecialchars(implode(' | ', $instScore)) . "</div>";
    }
    // edu notes use same bulletList, override font-size via liStyle
    $notes = array_values(array_filter($edu['notes'] ?? [], fn($v) => trim((string)$v) !== ''));
    if (!empty($notes)) {
        $li = '';
        foreach ($notes as $note) {
            $li .= "<li style='font-size:11px;margin-bottom:3px;'>" . htmlspecialchars($note) . "</li>";
        }
        $html .= "<ul style='margin:5px 0 0 0;padding-left:16px;'>{$li}</ul>";
    }
    return $html;
}

// ── Section heading style ─────────────────────────────────────────────────────
// font-size matches resume_preview.php (.sec-title = 13px)
// display:block forces Dompdf to treat it as block-level for border-bottom
$sl = "display:block;font-size:13px;font-weight:bold;letter-spacing:0.5px;"
    . "text-transform:uppercase;border-bottom:1.4px solid #1a1a1a;"
    . "padding-bottom:4px;margin-bottom:10px;margin-top:0;";

// ── Spacer helper — use margin-top on next element instead of empty divs ──────
// Dompdf collapses empty <div style='height:Xpx'> unpredictably.
// We use margin-bottom on section wrappers instead (see $secWrap below).
// FIX (page-overflow bug): with the real margin wrapper in place (see below),
// content wraps to slightly more lines than before, which pushed total height
// just past one A4 page (volunteering section spilled onto page 2). Section
// gaps tightened from 16px to 12px to reclaim vertical space without making
// the layout look cramped.
$secWrap     = "margin-bottom:12px;";   // wraps every section
$secWrapLast = "margin-bottom:0;";      // last section gets no bottom margin

// ── Header ────────────────────────────────────────────────────────────────────
$name = htmlspecialchars($user['name'] ?? '');

$contactParts = array_filter([
    $user['location'] ?? '',
    $user['phone']   ?? '',
    $user['email']   ?? '',
], fn($v) => $v !== '');
$contactLine = htmlspecialchars(implode(' | ', $contactParts));

$linkDefs = [
    ['key' => 'linkedin',  'label' => 'LinkedIn'],
    ['key' => 'github',    'label' => 'GitHub'],
    ['key' => 'portfolio', 'label' => 'Portfolio'],
];
$linkParts = [];
foreach ($linkDefs as $ld) {
    if (!empty($user[$ld['key']])) {
        $url         = htmlspecialchars($user[$ld['key']]);
        $label       = htmlspecialchars($ld['label']);
        $linkParts[] = "<a href='{$url}' style='color:#1a1a1a;font-weight:bold;text-decoration:underline;'>{$label}</a>";
    }
}
$linksLine = implode(" <span style='margin:0 6px;color:#555;'>|</span> ", $linkParts);

// ── Summary ───────────────────────────────────────────────────────────────────
$summarySection = '';
if (!empty($user['summary'])) {
    $summarySection =
        "<div style='{$secWrap}'>"
        . "<div style='{$sl}'>Summary</div>"
        . "<div style='font-size:11.5px;line-height:1.6;color:#222;'>" . htmlspecialchars($user['summary']) . "</div>"
        . "</div>";
}

// ── Education ─────────────────────────────────────────────────────────────────
$eduRowsHtml = '';
foreach (array_chunk($education, 2) as $chunk) {
    $eduRowsHtml .= '<tr>';
    if (count($chunk) === 2) {
        $eduRowsHtml .= "<td width='50%' valign='top' style='border-right:1px solid #ccc;padding:0 20px 10px 0;'>" . eduCell($chunk[0]) . "</td>";
        $eduRowsHtml .= "<td width='50%' valign='top' style='padding:0 0 10px 20px;'>" . eduCell($chunk[1]) . "</td>";
    } else {
        $eduRowsHtml .= "<td colspan='2' valign='top' style='padding-bottom:10px;'>" . eduCell($chunk[0]) . "</td>";
    }
    $eduRowsHtml .= '</tr>';
}
$eduSection = '';
if ($eduRowsHtml !== '') {
    $eduSection =
        "<div style='{$secWrap}'>"
        . "<div style='{$sl}'>Education</div>"
        . "<table width='100%' cellpadding='0' cellspacing='0' style='table-layout:fixed;'>{$eduRowsHtml}</table>"
        . "</div>";
}

// ── Skills ────────────────────────────────────────────────────────────────────
$skillsItemsHtml = '';
foreach ($skills as $s) {
    if (trim($s['items'] ?? '') === '') continue;
    $cat             = htmlspecialchars($s['category'] ?? '');
    $vals            = htmlspecialchars($s['items']);
    $skillsItemsHtml .= "<li style='margin-bottom:5px;font-size:11.5px;line-height:1.5;'>"
        . ($cat !== '' ? "<strong>{$cat}:</strong> " : '') . $vals . "</li>";
}
$skillsSection = '';
if ($skillsItemsHtml !== '') {
    $skillsSection =
        "<div style='{$secWrap}'>"
        . "<div style='{$sl}'>Skills</div>"
        . "<ul style='margin:0;padding-left:18px;'>{$skillsItemsHtml}</ul>"
        . "</div>";
}

// ── Projects ──────────────────────────────────────────────────────────────────
$projHtml = '';
foreach ($projects as $p) {
    if (trim($p['title'] ?? '') === '') continue;

    $title = htmlspecialchars($p['title']);
    $tech  = !empty($p['tech']) ? ' (' . htmlspecialchars($p['tech']) . ')' : '';

    $linkBits = [];
    foreach (($p['links'] ?? []) as $link) {
        if (empty($link['label']) || empty($link['url'])) continue;
        $label      = htmlspecialchars($link['label']);
        $url        = htmlspecialchars($link['url']);
        $linkBits[] = "<a href='{$url}' style='color:#1a1a1a;font-weight:bold;text-decoration:underline;'>{$label}</a>";
    }
    $projLinksHtml = implode(" <span style='margin:0 5px;color:#555;'>|</span> ", $linkBits);

    $bulletsHtml = bulletList($p['bullets'] ?? []);

    $projHtml .=
        "<div style='margin-bottom:9px;'>"
        . "<table width='100%' cellpadding='0' cellspacing='0' style='table-layout:fixed;'>"
        . "<tr>"
        . "<td style='font-size:12px;font-weight:bold;'>{$title}{$tech}</td>"
        . "<td align='right' style='font-size:11.5px;white-space:nowrap;width:auto;'>{$projLinksHtml}</td>"
        . "</tr>"
        . "</table>"
        . $bulletsHtml
        . "</div>";
}
$projSection = '';
if ($projHtml !== '') {
    $projSection =
        "<div style='{$secWrap}'>"
        . "<div style='{$sl}'>Projects</div>"
        . $projHtml
        . "</div>";
}

// ── Certifications ────────────────────────────────────────────────────────────
$certItemsHtml = '';
foreach ($certifications as $cert) {
    if (trim($cert['title'] ?? '') === '') continue;
    $title         = htmlspecialchars($cert['title']);
    $desc          = htmlspecialchars($cert['description'] ?? '');
    $certItemsHtml .= "<li style='margin-bottom:8px;'>"
        . "<div style='font-size:12px;font-weight:bold;'>{$title}</div>"
        . ($desc !== '' ? "<div style='font-size:11.5px;color:#333;margin-top:1px;'>{$desc}</div>" : '')
        . "</li>";
}
$certSection = '';
if ($certItemsHtml !== '') {
    $certSection =
        "<div style='{$secWrap}'>"
        . "<div style='{$sl}'>Certifications and Training</div>"
        . "<ul style='margin:0;padding-left:18px;'>{$certItemsHtml}</ul>"
        . "</div>";
}

// ── Volunteering ──────────────────────────────────────────────────────────────
$volItemsHtml = '';
foreach ($volunteering as $vol) {
    if (trim($vol['role'] ?? '') === '') continue;
    $role = htmlspecialchars($vol['role']);
    $org  = htmlspecialchars($vol['organization'] ?? '');
    $year = htmlspecialchars($vol['year'] ?? '');

    $header = "<strong>{$role}</strong>";
    if ($org  !== '') $header .= " | {$org}";
    if ($year !== '') $header .= " ({$year})";

    $bullets = bulletList($vol['bullets'] ?? []);

    $volItemsHtml .= "<li style='margin-bottom:10px;'>"
        . "<div style='font-size:11.5px;'>{$header}</div>"
        . $bullets
        . "</li>";
}
$volSection = '';
if ($volItemsHtml !== '') {
    $volSection =
        "<div style='{$secWrapLast}'>"
        . "<div style='{$sl}'>Volunteering Experience</div>"
        . "<ol style='margin:0;padding-left:18px;'>{$volItemsHtml}</ol>"
        . "</div>";
}

// ── Assemble full HTML ────────────────────────────────────────────────────────
//
// FIX (margin/scaling bug): Dompdf's @page { margin: ... } rule is not always
// honored consistently once $dompdf->setPaper() is called explicitly — the
// previous version relied on @page margin alone, which produced a PDF where
// content ran flush to all four edges with no border, unlike the preview's
// .resume { padding: 50px 55px } frame.
//
// Fix: keep a *small* @page margin only as a printer-safety fallback, and do
// the real visual margin explicitly with a padded wrapper div, exactly the
// way resume_preview.php pads its .resume container. This guarantees Dompdf
// renders the same margin every time, regardless of @page quirks, and keeps
// the PDF visually identical to the HTML preview.
$html  = '<!DOCTYPE html>';
$html .= '<html><head><meta charset="UTF-8">';
$html .= '<style>';
$html .= '@page { size: A4 portrait; margin: 0; }'; // margin handled by wrapper div below
$html .= '* { box-sizing:border-box; margin:0; padding:0; }';
$html .= "body { font-family:'Times New Roman', Georgia, serif; font-size:12px; color:#1a1a1a; }";
$html .= 'table { border-collapse:collapse; width:100%; }';
// Dompdf needs list-style on li, not ul, and needs padding-left on the ul
$html .= 'ul { list-style-type:disc; }';
$html .= 'ol { list-style-type:decimal; }';
$html .= 'a  { text-decoration:underline; }';
$html .= '</style></head><body>';

// Explicit margin wrapper — 15mm top/bottom, 14mm left/right, matching the
// original @page intent (slightly tightened from 18mm/16mm to reclaim space
// and avoid pushing content onto a second page), applied as real box padding
// so it always renders regardless of Dompdf's @page quirks.
$html .= "<div style='padding:15mm 14mm;'>";

// Header — font-weight:400 is more reliable in Dompdf than "normal"
$html .= "<div style='font-size:28px;font-weight:400;margin-bottom:6px;'>{$name}</div>";
if ($contactLine !== '') {
    $html .= "<div style='font-size:11.5px;color:#333;margin-bottom:6px;'>{$contactLine}</div>";
}
if ($linksLine !== '') {
    $html .= "<div style='font-size:11.5px;margin-bottom:14px;'>{$linksLine}</div>";
}
$html .= "<div style='border-top:1px solid #1a1a1a;margin-bottom:14px;'></div>";

// Sections
$html .= $summarySection
       . $eduSection
       . $skillsSection
       . $projSection
       . $certSection
       . $volSection;

$html .= "</div>"; // close margin wrapper

$html .= '</body></html>';

// ── Dompdf render ─────────────────────────────────────────────────────────────
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Times New Roman');
$options->set('dpi', 96);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('resume.pdf', ['Attachment' => true]);