<?php
$templatePath = __DIR__ . '/../' . $previewTemplate;
$html = is_file($templatePath) ? file_get_contents($templatePath) : '';

if ($html === '') {
    http_response_code(500);
    echo 'Template preview file not found.';
    return;
}

function tpl_date_range($row)
{
    $start = !empty($row['start_date']) ? date('M Y', strtotime($row['start_date'])) : '';
    $end = !empty($row['currently_working']) ? 'Present' : (!empty($row['end_date']) ? date('M Y', strtotime($row['end_date'])) : '');
    return trim($start . ($end ? ' - ' . $end : ''));
}

function tpl_project_url($project)
{
    return !empty($project['demo_url']) ? $project['demo_url'] : (!empty($project['github_url']) ? $project['github_url'] : '#');
}

function tpl_tech_tags($techs, $class = '')
{
    $out = '';
    foreach (array_filter(array_map('trim', explode(',', $techs ?? ''))) as $tech) {
        $out .= '<span' . ($class ? ' class="' . h($class) . '"' : '') . '>' . h($tech) . '</span>';
    }
    return $out;
}

function tpl_edit_bar()
{
    return '
<div class="edit-bar">
  <a href="choose-template.php">Change Template</a>
  <a href="edit-portfolio.php">Edit Portfolio</a>
</div>
<style>
  .edit-bar{position:fixed;right:20px;bottom:20px;z-index:9999;display:flex;gap:10px}
  .edit-bar a{padding:10px 14px;border-radius:10px;background:#fff;border:1px solid rgba(0,0,0,.15);color:#222;text-decoration:none;font:700 13px Arial,sans-serif;box-shadow:0 10px 30px rgba(0,0,0,.12)}
  .edit-bar a:last-child{background:#36402c;color:#fff;border-color:#36402c}
</style>';
}

$html = preg_replace('/<title>.*?<\/title>/is', '<title>' . h($name) . ' - Portfolio</title>', $html, 1);

switch ((int) $previewTemplateId) {
    case 2:
        $skillsHtml = '';
        $chunks = array_chunk($skills ?: [['skill_name' => 'Add skills in Edit Portfolio']], 4);
        foreach ($chunks as $idx => $chunk) {
            $colors = ['blue', 'teal', 'plum', 'amber'];
            $skillsHtml .= '<div class="skill-group" data-color="' . $colors[$idx % 4] . '"><h3>Skill Set ' . ($idx + 1) . '</h3><div class="ring-row">';
            foreach ($chunk as $skill) {
                $skillsHtml .= '<div class="ring-item"><svg class="ring" viewBox="0 0 100 100"><circle class="ring-track" cx="50" cy="50" r="42"></circle><circle class="ring-fill" cx="50" cy="50" r="42" stroke-dasharray="263.89" stroke-dashoffset="39.58"></circle><text x="50" y="55" class="ring-text">85%</text></svg><span class="ring-label">' . h($skill['skill_name']) . '</span></div>';
            }
            $skillsHtml .= '</div></div>';
        }

        $experienceRows = '';
        foreach ($experience as $exp) {
            $experienceRows .= '<div class="ledger-row"><div class="ledger-date">' . h(tpl_date_range($exp)) . '</div><div class="ledger-body"><h3>' . h($exp['position']) . '</h3><span class="org">' . h($exp['company']) . '</span><p>' . nl2br(h($exp['description'] ?? '')) . '</p></div></div>';
        }
        if ($experienceRows === '') {
            $experienceRows = '<div class="ledger-row"><div class="ledger-date">Now</div><div class="ledger-body"><h3>No experience added yet</h3><span class="org">Edit Portfolio</span><p>Add experience to populate this section.</p></div></div>';
        }

        $projectRows = '';
        foreach ($projects as $project) {
            $projectRows .= '<div class="project-card"><div class="project-info"><h3>' . h($project['title']) . '</h3><p>' . h($project['description'] ?? '') . '</p><div class="stack-row">' . tpl_tech_tags($project['technologies'] ?? '') . '</div></div><a class="project-link" href="' . h(tpl_project_url($project)) . '" target="_blank" rel="noopener">View Project</a></div>';
        }
        if ($projectRows === '') {
            $projectRows = '<div class="project-card"><div class="project-info"><h3>No projects added yet</h3><p>Add projects in Edit Portfolio to populate this section.</p></div><a class="project-link" href="edit-portfolio.php">Add Project</a></div>';
        }

        $contactForm = !empty($profile['enable_contact_form'])
            ? '<form action="contact-handler.php" method="POST" class="contact-form" novalidate><input type="hidden" name="portfolio_user_id" value="' . (int) $userId . '"><div class="form-row"><label for="name">Name</label><input type="text" id="name" name="name" required maxlength="100"></div><div class="form-row"><label for="email">Email</label><input type="email" id="email" name="email" required maxlength="150"></div><div class="form-row"><label for="subject">Subject</label><input type="text" id="subject" name="subject" maxlength="150"></div><div class="form-row"><label for="message">Message</label><textarea id="message" name="message" rows="5" required maxlength="5000"></textarea></div><div class="form-actions"><button type="submit">Send Message</button><span class="form-status" role="status" aria-live="polite"></span></div></form>'
            : ($contactEmail ? '<p class="lead"><a class="project-link" href="mailto:' . h($contactEmail) . '">Email me</a></p>' : '');

        $html = preg_replace('/<h1>.*?<\/h1>/is', '<h1>' . h($name) . '</h1>', $html, 1);
        $html = preg_replace('/<p class="role">.*?<\/p>/is', '<p class="role">' . h($title) . '</p>', $html, 1);
        $html = preg_replace('/<p class="blurb">.*?<\/p>/is', '<p class="blurb">' . h($bio) . '</p>', $html, 1);
        $html = preg_replace('/<span class="contact-line">.*?<\/span>\s*<span class="contact-line">.*?<\/span>/is', '<span class="contact-line">' . h($location) . '</span><span class="contact-line">' . h($contactEmail) . '</span>', $html, 1);
        $html = preg_replace('/<section id="about">.*?<\/section>/is', '<section id="about"><div class="section-head"><span class="tag">01</span><h2>About</h2></div><p>' . nl2br(h($bio)) . '</p></section>', $html, 1);
        $html = preg_replace('/<div class="skills-grid">.*?<\/div>\s*<\/section>/is', '<div class="skills-grid">' . $skillsHtml . '</div></section>', $html, 1);
        $html = preg_replace('/<div class="ledger">.*?<\/div>\s*<\/section>/is', '<div class="ledger">' . $experienceRows . '</div></section>', $html, 1);
        $html = preg_replace('/<div class="projects-list">.*?<\/div>\s*<\/section>/is', '<div class="projects-list">' . $projectRows . '</div></section>', $html, 1);
        $html = preg_replace('/<div class="contact-grid">.*?<\/div>\s*<form id="contact-form".*?<\/form>/is', '<div class="contact-grid"><div class="contact-item"><span class="label">Email</span><span class="value">' . h($contactEmail) . '</span></div><div class="contact-item"><span class="label">Phone</span><span class="value">' . h($contactPhone) . '</span></div><div class="contact-item"><span class="label">Location</span><span class="value">' . h($location) . '</span></div><div class="contact-item"><span class="label">Availability</span><span class="value">Open to opportunities</span></div></div>' . $contactForm, $html, 1);
        $html = preg_replace('/<footer class="site-footer">.*?<\/footer>/is', '<footer class="site-footer">&copy; ' . date('Y') . ' ' . h($name) . '. Built with PortfolioBuilder.</footer>', $html, 1);
        $html = preg_replace('/<script>.*?<\/script>/is', '', $html);
        break;

    case 3:
        $projectRows = '';
        foreach ($projects as $idx => $project) {
            $tags = '';
            foreach (array_filter(array_map('trim', explode(',', $project['technologies'] ?? ''))) as $tech) {
                $tags .= '<li>' . h($tech) . '</li>';
            }
            $projectRows .= '<article class="project"><div class="project-media"><div style="height:240px;border-radius:var(--radius);border:1px solid var(--line);background:var(--accent-soft);display:grid;place-items:center;color:var(--accent);font-family:var(--mono);font-size:42px;">0' . (($idx % 9) + 1) . '</div></div><div class="project-info"><h3>' . h($project['title']) . '</h3><p>' . h($project['description'] ?? '') . '</p><ul class="tag-list">' . $tags . '</ul><a class="project-link" href="' . h(tpl_project_url($project)) . '" target="_blank" rel="noopener">View project</a></div></article>';
        }
        if ($projectRows === '') {
            $projectRows = '<article class="project"><div class="project-info"><h3>No projects added yet</h3><p>Add projects in Edit Portfolio to populate this section.</p></div></article>';
        }

        $stackGroups = '';
        foreach (array_chunk($skills ?: [['skill_name' => 'Add skills in Edit Portfolio']], 4) as $idx => $chunk) {
            $stackGroups .= '<div class="stack-group"><h3>group ' . ($idx + 1) . '</h3><ul class="chip-list">';
            foreach ($chunk as $skill) {
                $stackGroups .= '<li>' . h($skill['skill_name']) . '</li>';
            }
            $stackGroups .= '</ul></div>';
        }

        $form = !empty($profile['enable_contact_form'])
            ? '<form action="contact-handler.php" method="POST" class="contact-form" novalidate><input type="hidden" name="portfolio_user_id" value="' . (int) $userId . '"><div class="form-row"><label for="name">Name</label><input type="text" id="name" name="name" required maxlength="100"></div><div class="form-row"><label for="email">Email</label><input type="email" id="email" name="email" required maxlength="150"></div><div class="form-row form-row-full"><label for="message">Message</label><textarea id="message" name="message" rows="5" required maxlength="5000"></textarea></div><div class="form-actions"><button type="submit">Send message</button><span class="form-status" role="status" aria-live="polite"></span></div></form>'
            : '';

        $brand = strtolower(preg_replace('/[^a-z0-9]+/i', '.', trim($firstName))) ?: 'portfolio';
        $html = str_replace('jordan<span class="brand-dot">.</span>lee', h($brand) . '<span class="brand-dot">.</span>', $html);
        $html = preg_replace('/<span class="terminal-title">.*?<\/span>/is', '<span class="terminal-title">' . h(strtolower($firstName)) . '@dev: ~</span>', $html, 1);
        $html = preg_replace('/<p class="line output">.*?<\/p>/is', '<p class="line output">' . h($name) . ' - ' . h($title) . '</p>', $html, 1);
        $html = preg_replace('/<p class="hero-sub">.*?<\/p>/is', '<p class="hero-sub">' . h($bio) . '</p>', $html, 1);
        $html = preg_replace('/<div class="project-list">.*?<\/div>\s*<\/section>/is', '<div class="project-list">' . $projectRows . '</div></section>', $html, 1);
        $html = preg_replace('/<div class="about-grid">.*?<\/div>\s*<\/section>/is', '<div class="about-grid"><div class="about-photo">' . $avatarHtml . '</div><div class="about-copy"><p class="eyebrow">// about</p><h2 class="section-title">' . h($name) . '</h2><p>' . nl2br(h($bio)) . '</p><ul class="meta-list"><li><span>Location</span>' . h($location) . '</li><li><span>Experience</span>' . h($expYears) . '</li><li><span>Focus</span>' . h($title) . '</li></ul></div></div></section>', $html, 1);
        $html = preg_replace('/<div class="stack-grid">.*?<\/div>\s*<\/section>/is', '<div class="stack-grid">' . $stackGroups . '</div></section>', $html, 1);
        $html = preg_replace('/<form id="contact-form".*?<\/form>/is', $form, $html, 1);
        $html = preg_replace('/<div class="social-row">.*?<\/div>/is', '<div class="social-row"><a href="mailto:' . h($contactEmail) . '">' . h($contactEmail) . '</a>' . $socialLinksHtml . '</div>', $html, 1);
        $html = preg_replace('/<footer class="site-footer wrap">.*?<\/footer>/is', '<footer class="site-footer wrap"><span>&copy; ' . date('Y') . ' ' . h($name) . '</span><a href="#top" class="back-to-top">Back to top</a></footer>', $html, 1);
        $html = preg_replace('/loadProjects\(\);\s*loadTestimonials\(\);/s', '', $html);
        break;

    case 4:
        $skillData = [];
        foreach ($skills as $skill) {
            $skillData[] = ['name' => $skill['skill_name'], 'pct' => 85, 'icon' => '*', 'cardGrad' => 'linear-gradient(135deg,#7c3aed,#ec4899)', 'barGrad' => 'linear-gradient(90deg,#7c3aed,#ec4899)'];
        }
        if (!$skillData) $skillData[] = ['name' => 'Add skills in Edit Portfolio', 'pct' => 50, 'icon' => '*', 'cardGrad' => 'linear-gradient(135deg,#7c3aed,#ec4899)', 'barGrad' => 'linear-gradient(90deg,#7c3aed,#ec4899)'];

        $projectData = [];
        foreach ($projects as $project) {
            $tagList = [];
            foreach (array_slice(array_filter(array_map('trim', explode(',', $project['technologies'] ?? ''))), 0, 3) as $tech) {
                $tagList[] = [$tech, 'tag-purple'];
            }
            $projectData[] = ['emoji' => '#', 'thumbGrad' => 'linear-gradient(135deg,#7c3aed,#ec4899)', 'tags' => $tagList, 'title' => $project['title'], 'desc' => $project['description'] ?? '', 'url' => tpl_project_url($project)];
        }

        $timelineData = [];
        foreach ($experience as $exp) {
            $timelineData[] = ['date' => tpl_date_range($exp), 'role' => $exp['position'], 'company' => $exp['company'], 'desc' => $exp['description'] ?? '', 'dotGrad' => 'linear-gradient(135deg,#7c3aed,#ec4899)'];
        }

        $html = str_replace('&lt;YourName /&gt;', '&lt;' . h($firstName) . ' /&gt;', $html);
        $html = str_replace("'Your Name'", "'" . h($name) . "'", $html);
        $html = str_replace("'Web Developer'", "'" . h($title) . "'", $html);
        $html = preg_replace('/<p class="hero-desc">.*?<\/p>/is', '<p class="hero-desc">' . h($bio) . '</p>', $html, 1);
        $html = preg_replace('/<div class="stat-num">3\+<\/div>/', '<div class="stat-num">' . h($expYears) . '</div>', $html, 1);
        $html = preg_replace('/<div class="stat-num">40\+<\/div>/', '<div class="stat-num">' . count($projects) . '+</div>', $html, 1);
        $html = preg_replace('/<div class="stat-num">20\+<\/div>/', '<div class="stat-num">' . count($skills) . '+</div>', $html, 1);
        $html = preg_replace('/const skills = \[.*?\];/s', 'const skills = ' . json_encode($skillData) . ';', $html, 1);
        $html = preg_replace('/const projects = \[.*?\];/s', 'const projects = ' . json_encode($projectData) . ';', $html, 1);
        $html = preg_replace('/const timeline = \[.*?\];/s', 'const timeline = ' . json_encode($timelineData) . ';', $html, 1);
        $html = preg_replace('/<form class="contact-form".*?<\/form>/is', '<form action="contact-handler.php" method="POST" class="contact-form"><input type="hidden" name="portfolio_user_id" value="' . (int) $userId . '"><div class="form-group"><label>Name</label><input type="text" name="name" required></div><div class="form-group"><label>Email Address</label><input type="email" name="email" required></div><div class="form-group"><label>Subject</label><input type="text" name="subject"></div><div class="form-group"><label>Message</label><textarea name="message" required></textarea></div><button type="submit" class="btn-send">Send Message</button></form>', $html, 1);
        $html = preg_replace('/<div class="footer-copy">.*?<\/div>/is', '<div class="footer-copy">&copy; ' . date('Y') . ' ' . h($name) . '</div>', $html, 1);
        break;

    case 5:
        $projectCards = '';
        foreach ($projects as $idx => $project) {
            $projectCards .= '<div class="project-card"><div class="thumb-icon thumb-' . (($idx % 6) + 1) . '">#</div><div class="project-info"><div class="project-tag">' . h($project['technologies'] ?? 'Project') . '</div><div class="project-name">' . h($project['title']) . '</div><div class="project-date">' . h($project['description'] ?? '') . '</div></div></div>';
        }
        if ($projectCards === '') {
            $projectCards = '<div class="project-card"><div class="project-info"><div class="project-tag">Portfolio</div><div class="project-name">No projects added yet</div><div class="project-date">Add projects in Edit Portfolio</div></div></div>';
        }

        $html = str_replace('Olivia', h($firstName), $html);
        $html = preg_replace('/<h1>Hi there, I\'m <span>.*?<\/span><\/h1>/is', '<h1>Hi there, I\'m <span>' . h($firstName) . '!</span></h1>', $html, 1);
        $html = preg_replace('/<div class="hero-badge">.*?<\/div>/is', '<div class="hero-badge">' . h($title) . '</div>', $html, 1);
        $html = preg_replace('/<p>\s*I\'m a passionate.*?<\/p>/is', '<p>' . h($bio) . '</p>', $html, 1);
        $html = preg_replace('/<div class="projects-grid">.*?<\/div>\s*<\/section>/is', '<div class="projects-grid">' . $projectCards . '</div></section>', $html, 1);
        $html = preg_replace('/<div class="contact-info">.*?<\/div>\s*<form class="contact-form"/is', '<div class="contact-info"><div class="section-label">Contact</div><h2 class="section-title">Get in touch</h2><p>' . h($bio) . '</p><div class="contact-socials">' . $socialLinksHtml . ($contactEmail ? '<a href="mailto:' . h($contactEmail) . '" class="social-btn" title="Email">@</a>' : '') . '</div></div><form action="contact-handler.php" method="POST" class="contact-form"', $html, 1);
        $html = str_replace('<form action="contact-handler.php" method="POST" class="contact-form"', '<form action="contact-handler.php" method="POST" class="contact-form"><input type="hidden" name="portfolio_user_id" value="' . (int) $userId . '">', $html);
        $html = preg_replace('/<footer>.*?<\/footer>/is', '<footer><p>' . h($name) . ' &copy; ' . date('Y') . ' - All rights reserved.</p></footer>', $html, 1);
        break;

    case 6:
        $skillBars = '';
        foreach (array_slice($skills, 0, 6) as $skill) {
            $skillBars .= '<div class="bar-item"><div class="bar-label"><span>' . h($skill['skill_name']) . '</span><span>85%</span></div><div class="bar-track"><div class="bar-fill" style="width:85%; background:#5B3FD6;"></div></div></div>';
        }
        if ($skillBars === '') $skillBars = '<div class="bar-item"><div class="bar-label"><span>Add skills in Edit Portfolio</span><span>50%</span></div><div class="bar-track"><div class="bar-fill" style="width:50%; background:#5B3FD6;"></div></div></div>';

        $projectTiles = '';
        foreach ($projects as $project) {
            $img = !empty($project['image_path']) ? '<img src="' . h($project['image_path']) . '" alt="' . h($project['title']) . '">' : '<span style="display:grid;place-items:center;height:100%;background:var(--purple-soft);color:var(--purple);font-weight:800;">' . h($project['title']) . '</span>';
            $projectTiles .= '<a href="' . h(tpl_project_url($project)) . '" class="project-tile" data-cat="uiux" target="_blank" rel="noopener">' . $img . '<span class="tile-overlay"><span class="tile-icon" aria-hidden="true">&#128065;</span></span></a>';
        }
        if ($projectTiles === '') $projectTiles = '<a href="edit-portfolio.php" class="project-tile" data-cat="uiux"><span style="display:grid;place-items:center;height:100%;background:var(--purple-soft);color:var(--purple);font-weight:800;">Add projects</span></a>';

        $html = str_replace('Alex Rivera', h($name), $html);
        $html = str_replace('ProDev', h($firstName), $html);
        $html = preg_replace('/<p class="hero-role">.*?<\/p>/is', '<p class="hero-role">' . h($title) . '</p>', $html, 1);
        $html = preg_replace('/<p class="about-desc">.*?<\/p>/is', '<p class="about-desc">' . h($bio) . '</p>', $html, 1);
        $html = preg_replace('/<span class="years-number">.*?<\/span>/is', '<span class="years-number">' . preg_replace('/[^0-9]/', '', $expYears ?: '0') . '</span>', $html, 1);
        $html = preg_replace('/<div class="bar-list">.*?<\/div>\s*<\/div>\s*<div class="skills-right">/is', '<div class="bar-list">' . $skillBars . '</div></div><div class="skills-right">', $html, 1);
        $html = preg_replace('/<div class="project-grid">.*?<\/div>\s*<\/section>/is', '<div class="project-grid">' . $projectTiles . '</div></section>', $html, 1);
        $html = preg_replace('/<strong>123 Street, New York, USA<\/strong>/is', '<strong>' . h($location) . '</strong>', $html, 1);
        $html = preg_replace('/<strong>\+012 345 6789<\/strong>/is', '<strong>' . h($contactPhone) . '</strong>', $html, 1);
        $html = preg_replace('/<strong>info@example.com<\/strong>/is', '<strong>' . h($contactEmail) . '</strong>', $html, 1);
        $html = preg_replace('/<form id="contact-form".*?<\/form>/is', '<form id="contact-form" action="contact-handler.php" method="POST" class="contact-form" novalidate><input type="hidden" name="portfolio_user_id" value="' . (int) $userId . '"><div class="form-row-2"><div class="form-row"><input type="text" name="name" placeholder="Your name" required maxlength="100"></div><div class="form-row"><input type="email" name="email" placeholder="Your email" required maxlength="150"></div></div><div class="form-row"><input type="text" name="subject" placeholder="Subject" maxlength="150"></div><div class="form-row"><textarea name="message" rows="5" placeholder="Message" required maxlength="5000"></textarea></div><div class="form-actions"><button type="submit">Send message</button><span class="form-status" role="status" aria-live="polite"></span></div></form>', $html, 1);
        $html = preg_replace('/<span>.*?All rights reserved\.<\/span>/is', '<span>&copy; ' . date('Y') . ' ' . h($name) . '. All rights reserved.</span>', $html, 1);
        break;
}

$html = str_replace('</body>', tpl_edit_bar() . "\n</body>", $html);
echo $html;
