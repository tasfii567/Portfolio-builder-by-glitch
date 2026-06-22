<?php include 'resume_data.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="color-scheme" content="light only">
<title><?php echo htmlspecialchars($user['name'] ?? 'Resume'); ?> — Resume</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  html { color-scheme: light only; }

  body {
    font-family: 'Times New Roman', Georgia, serif;
    font-size: 12px;
    color: #1a1a1a;
    background: #c8c8c8;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 30px 20px;
  }

  /* A4 width fixed, height grows with content */
  .resume {
    width: 794px;
    min-height: 1123px;
    background: #fff;
    color: #1a1a1a;
    box-shadow: 0 2px 16px rgba(0,0,0,0.2);
    padding: 50px 55px;
  }

  /* ── Header ── */
  .name { font-size: 30px; font-weight: normal; letter-spacing: 0.5px; margin-bottom: 6px; color: #1a1a1a; }
  .contact-line { font-size: 11.5px; color: #333; margin-bottom: 6px; }
  .links-line { font-size: 11.5px; margin-bottom: 16px; color: #1a1a1a; }
  .links-line a { color: #1a1a1a; font-weight: bold; text-decoration: underline; }
  .links-line a:visited { color: #1a1a1a; }
  .links-line .sep { margin: 0 6px; color: #555; font-weight: normal; }
  .header-rule { border: none; border-top: 1px solid #1a1a1a; margin-bottom: 18px; }

  /* ── Section heading ── */
  .sec { margin-bottom: 18px; }
  .sec:last-child { margin-bottom: 0; }
  .sec-title {
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border-bottom: 1.4px solid #1a1a1a;
    padding-bottom: 4px;
    margin-bottom: 10px;
    color: #1a1a1a;
  }

  .summary-text { font-size: 11.5px; line-height: 1.6; color: #222; }

  /* ── Education ── */
  .edu-grid { display: grid; grid-template-columns: 1fr 1fr; column-gap: 24px; row-gap: 14px; }
  .edu-item.has-divider { border-right: 1px solid #ccc; padding-right: 20px; }
  .edu-item.full-span { grid-column: 1 / -1; }
  .edu-line { font-size: 11.5px; color: #1a1a1a; }
  .edu-line.inst-line { margin-top: 2px; color: #333; }
  .edu-notes { margin-top: 5px; padding-left: 16px; }
  .edu-notes li { margin-bottom: 3px; font-size: 11px; color: #333; }

  /* ── Skills ── */
  .skills-list { list-style: disc; padding-left: 18px; }
  .skills-list li { margin-bottom: 5px; font-size: 11.5px; line-height: 1.5; color: #1a1a1a; }
  .skill-cat { font-weight: bold; color: #1a1a1a; }

  /* ── Projects ── */
  .proj-entry { margin-bottom: 14px; }
  .proj-entry:last-child { margin-bottom: 0; }
  .proj-head { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 4px 10px; }
  .proj-title { font-weight: bold; font-size: 12px; color: #1a1a1a; }
  .proj-links { font-size: 11.5px; white-space: nowrap; color: #1a1a1a; }
  .proj-links a { color: #1a1a1a; font-weight: bold; text-decoration: underline; }
  .proj-links a:visited { color: #1a1a1a; }
  .proj-links .sep { margin: 0 5px; color: #555; font-weight: normal; }
  .proj-bullets { padding-left: 18px; margin-top: 4px; }
  .proj-bullets li { margin-bottom: 3px; font-size: 11.5px; line-height: 1.5; color: #1a1a1a; }

  /* ── Certifications ── */
  .cert-list { list-style: disc; padding-left: 18px; }
  .cert-list li { margin-bottom: 8px; color: #1a1a1a; }
  .cert-list li:last-child { margin-bottom: 0; }
  .cert-title { font-weight: bold; font-size: 12px; color: #1a1a1a; }
  .cert-desc { font-size: 11.5px; color: #333; margin-top: 1px; }

  /* ── Volunteering ── */
  .vol-list { padding-left: 18px; }
  .vol-list li { margin-bottom: 10px; color: #1a1a1a; }
  .vol-list li:last-child { margin-bottom: 0; }
  .vol-header { font-size: 11.5px; margin-bottom: 3px; color: #1a1a1a; }
  .vol-bullets { padding-left: 18px; }
  .vol-bullets li { font-size: 11.5px; line-height: 1.5; color: #1a1a1a; }

  /* ── Export button ── */
  .export-wrap { text-align: center; padding: 24px; }
  .export-btn {
    display: inline-block;
    padding: 10px 28px;
    background: #1a1a1a;
    color: #fff;
    text-decoration: none;
    font-family: Arial, sans-serif;
    font-size: 13px;
    border-radius: 4px;
    letter-spacing: 0.4px;
  }
  .export-btn:hover { background: #333; }
</style>
</head>
<body>

<?php
/**
 * Helper: filter out blank strings from an array.
 * Keeps the preview consistent with export_pdf.php's bulletList() guard.
 */
function filterNonEmpty(array $items): array {
    return array_values(array_filter($items, fn($v) => trim((string)$v) !== ''));
}
?>

<div class="resume">

  <!-- ── Header ── -->
  <div class="header">
    <h1 class="name"><?php echo htmlspecialchars($user['name'] ?? ''); ?></h1>

    <?php
      $contactParts = array_filter([
          $user['location'] ?? '',
          $user['phone']   ?? '',
          $user['email']   ?? '',
      ], fn($v) => $v !== '');
    ?>
    <?php if (!empty($contactParts)): ?>
    <div class="contact-line"><?php echo htmlspecialchars(implode(' | ', $contactParts)); ?></div>
    <?php endif; ?>

    <?php
      $linkDefs = [
          ['key' => 'linkedin',  'label' => 'LinkedIn'],
          ['key' => 'github',    'label' => 'GitHub'],
          ['key' => 'portfolio', 'label' => 'Portfolio'],
      ];
      $activeLinks = array_filter($linkDefs, fn($ld) => !empty($user[$ld['key']]));
    ?>
    <?php if (!empty($activeLinks)): ?>
    <div class="links-line">
      <?php $first = true; foreach ($activeLinks as $ld): ?>
        <?php if (!$first): ?><span class="sep">|</span><?php endif; ?>
        <a href="<?php echo htmlspecialchars($user[$ld['key']]); ?>"><?php echo htmlspecialchars($ld['label']); ?></a>
        <?php $first = false; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <hr class="header-rule">
  </div>

  <!-- ── Summary ── -->
  <?php if (!empty($user['summary'])): ?>
  <div class="sec">
    <div class="sec-title">Summary</div>
    <div class="summary-text"><?php echo htmlspecialchars($user['summary']); ?></div>
  </div>
  <?php endif; ?>

  <!-- ── Education ── -->
  <?php if (!empty($education)): ?>
  <?php $eduCount = count($education); ?>
  <div class="sec">
    <div class="sec-title">Education</div>
    <div class="edu-grid">
      <?php foreach ($education as $i => $edu): ?>
        <?php
          $isLastOdd  = ($eduCount % 2 === 1) && ($i === $eduCount - 1);
          $hasDivider = !$isLastOdd && ($i % 2 === 0) && isset($education[$i + 1]);
          $classes    = 'edu-item';
          if ($isLastOdd)  $classes .= ' full-span';
          if ($hasDivider) $classes .= ' has-divider';

          $degreeDur = array_filter([$edu['degree'] ?? '', $edu['duration'] ?? ''], fn($v) => $v !== '');
          $instScore = array_filter([$edu['institution'] ?? '', $edu['score'] ?? ''], fn($v) => $v !== '');

          // Guard: filter blank strings from notes (fixes inconsistency vs export_pdf.php)
          $notes = filterNonEmpty($edu['notes'] ?? []);
        ?>
        <div class="<?php echo $classes; ?>">
          <?php if (!empty($degreeDur)): ?>
          <div class="edu-line"><?php echo htmlspecialchars(implode(' | ', $degreeDur)); ?></div>
          <?php endif; ?>
          <?php if (!empty($instScore)): ?>
          <div class="edu-line inst-line"><?php echo htmlspecialchars(implode(' | ', $instScore)); ?></div>
          <?php endif; ?>
          <?php if (!empty($notes)): ?>
          <ul class="edu-notes">
            <?php foreach ($notes as $note): ?>
            <li><?php echo htmlspecialchars($note); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Skills ── -->
  <?php if (!empty($skills)): ?>
  <div class="sec">
    <div class="sec-title">Skills</div>
    <ul class="skills-list">
      <?php foreach ($skills as $skill): ?>
      <?php if (trim($skill['items'] ?? '') === '') continue; ?>
      <li>
        <?php if (!empty($skill['category'])): ?><span class="skill-cat"><?php echo htmlspecialchars($skill['category']); ?>:</span> <?php endif; ?>
        <?php echo htmlspecialchars($skill['items']); ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- ── Projects ── -->
  <?php if (!empty($projects)): ?>
  <div class="sec">
    <div class="sec-title">Projects</div>
    <?php foreach ($projects as $project): ?>
    <?php if (trim($project['title'] ?? '') === '') continue; ?>
    <div class="proj-entry">
      <div class="proj-head">
        <span class="proj-title">
          <?php echo htmlspecialchars($project['title']); ?><?php if (!empty($project['tech'])): ?> (<?php echo htmlspecialchars($project['tech']); ?>)<?php endif; ?>
        </span>
        <?php
          // Only render links with both a non-empty label AND a non-empty url
          $validLinks = array_filter(
              $project['links'] ?? [],
              fn($l) => !empty($l['label']) && !empty($l['url'])
          );
        ?>
        <?php if (!empty($validLinks)): ?>
        <span class="proj-links">
          <?php $idx = 0; foreach ($validLinks as $link): ?>
            <?php if ($idx > 0): ?><span class="sep">|</span><?php endif; ?>
            <a href="<?php echo htmlspecialchars($link['url']); ?>"><?php echo htmlspecialchars($link['label']); ?></a>
            <?php $idx++; ?>
          <?php endforeach; ?>
        </span>
        <?php endif; ?>
      </div>
      <?php $bullets = filterNonEmpty($project['bullets'] ?? []); ?>
      <?php if (!empty($bullets)): ?>
      <ul class="proj-bullets">
        <?php foreach ($bullets as $bullet): ?>
        <li><?php echo htmlspecialchars($bullet); ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ── Certifications and Training ── -->
  <?php if (!empty($certifications)): ?>
  <div class="sec">
    <div class="sec-title">Certifications and Training</div>
    <ul class="cert-list">
      <?php foreach ($certifications as $cert): ?>
      <?php if (trim($cert['title'] ?? '') === '') continue; ?>
      <li>
        <div class="cert-title"><?php echo htmlspecialchars($cert['title']); ?></div>
        <?php if (!empty($cert['description'])): ?>
        <div class="cert-desc"><?php echo htmlspecialchars($cert['description']); ?></div>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- ── Volunteering Experience ── -->
  <?php if (!empty($volunteering)): ?>
  <div class="sec">
    <div class="sec-title">Volunteering Experience</div>
    <ol class="vol-list">
      <?php foreach ($volunteering as $vol): ?>
      <?php if (trim($vol['role'] ?? '') === '') continue; ?>
      <li>
        <div class="vol-header">
          <strong><?php echo htmlspecialchars($vol['role']); ?></strong>
          <?php if (!empty($vol['organization'])): ?> | <?php echo htmlspecialchars($vol['organization']); ?><?php endif; ?>
          <?php if (!empty($vol['year'])): ?> (<?php echo htmlspecialchars($vol['year']); ?>)<?php endif; ?>
        </div>
        <?php $volBullets = filterNonEmpty($vol['bullets'] ?? []); ?>
        <?php if (!empty($volBullets)): ?>
        <ul class="vol-bullets">
          <?php foreach ($volBullets as $bullet): ?>
          <li><?php echo htmlspecialchars($bullet); ?></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
  <?php endif; ?>

</div>

<div class="export-wrap">
  <a class="export-btn" href="export_pdf.php">Export PDF</a>
</div>

</body>
</html>