<?php
declare(strict_types=1);

session_start();

$skillCatalog = [
    'Languages' => ['php', 'javascript', 'typescript', 'python', 'java', 'c#', 'c++', 'go', 'ruby', 'sql', 'html', 'css', 'bash'],
    'Backend' => ['laravel', 'symfony', 'wordpress', 'node.js', 'express', 'django', 'flask', 'spring', 'rest api', 'graphql', 'microservices', 'api integration'],
    'Frontend' => ['react', 'vue', 'angular', 'next.js', 'tailwind', 'bootstrap', 'jquery', 'responsive design', 'accessibility'],
    'Data' => ['mysql', 'postgresql', 'mongodb', 'redis', 'elasticsearch', 'etl', 'data analysis', 'pandas', 'power bi', 'tableau'],
    'Cloud & DevOps' => ['aws', 'azure', 'gcp', 'docker', 'kubernetes', 'ci/cd', 'github actions', 'linux', 'nginx', 'apache'],
    'AI & Automation' => ['machine learning', 'ai', 'openai', 'prompt engineering', 'chatbot', 'nlp', 'automation', 'recommendation system'],
    'Quality' => ['testing', 'unit testing', 'phpunit', 'jest', 'selenium', 'playwright', 'code review', 'security'],
    'Professional' => ['project management', 'communication', 'leadership', 'agile', 'scrum', 'client management', 'documentation'],
];

$synonyms = [
    'js' => 'javascript',
    'ts' => 'typescript',
    'nodejs' => 'node.js',
    'node' => 'node.js',
    'postgres' => 'postgresql',
    'mysql database' => 'mysql',
    'ci cd' => 'ci/cd',
    'llm' => 'ai',
    'large language model' => 'ai',
    'wp' => 'wordpress',
];

$defaultPortfolio = "Paste your portfolio, resume, LinkedIn About section, GitHub profile, or project descriptions here.\n\nExample:\nPHP developer with Laravel, MySQL, REST API, JavaScript, React, Docker, AWS, testing, and AI automation experience.";

$portfolio = trim((string)($_POST['portfolio'] ?? ($_SESSION['portfolio'] ?? $defaultPortfolio)));
$targetRole = trim((string)($_POST['target_role'] ?? ($_SESSION['target_role'] ?? 'PHP Developer')));
$location = trim((string)($_POST['location'] ?? ($_SESSION['location'] ?? 'Remote')));
$experience = trim((string)($_POST['experience'] ?? ($_SESSION['experience'] ?? 'mid')));
$jobText = trim((string)($_POST['job_text'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['portfolio'] = $portfolio;
    $_SESSION['target_role'] = $targetRole;
    $_SESSION['location'] = $location;
    $_SESSION['experience'] = $experience;
}

function normalize_text(string $text): string
{
    $text = strtolower($text);
    $text = str_replace(['+', '#'], [' plus ', ' sharp '], $text);
    $text = preg_replace('/[^\p{L}\p{N}\/\.\s-]+/u', ' ', $text) ?? $text;
    return preg_replace('/\s+/', ' ', $text) ?? $text;
}

function flatten_catalog(array $catalog): array
{
    $skills = [];
    foreach ($catalog as $group => $items) {
        foreach ($items as $skill) {
            $skills[$skill] = $group;
        }
    }
    return $skills;
}

function extract_skills(string $text, array $catalog, array $synonyms): array
{
    $normalized = normalize_text($text);
    foreach ($synonyms as $from => $to) {
        $normalized = preg_replace('/\b' . preg_quote($from, '/') . '\b/u', $to, $normalized) ?? $normalized;
    }

    $flat = flatten_catalog($catalog);
    $found = [];
    foreach ($flat as $skill => $group) {
        $needle = normalize_text($skill);
        $pattern = '/(?<![\p{L}\p{N}])' . preg_quote($needle, '/') . '(?![\p{L}\p{N}])/u';
        if (preg_match($pattern, $normalized)) {
            $found[$skill] = $group;
        }
    }

    ksort($found);
    return $found;
}

function rank_skills(array $skills, array $priorityWords): array
{
    $ranked = [];
    foreach ($skills as $skill => $group) {
        $score = 50;
        foreach ($priorityWords as $word) {
            if ($word !== '' && str_contains($skill, $word)) {
                $score += 25;
            }
        }
        if (in_array($group, ['Backend', 'AI & Automation', 'Cloud & DevOps'], true)) {
            $score += 10;
        }
        $ranked[] = ['skill' => $skill, 'group' => $group, 'score' => $score];
    }
    usort($ranked, fn($a, $b) => $b['score'] <=> $a['score'] ?: strcmp($a['skill'], $b['skill']));
    return $ranked;
}

function build_searches(string $role, string $location, string $experience, array $rankedSkills): array
{
    $top = array_slice(array_column($rankedSkills, 'skill'), 0, 6);
    $core = array_values(array_unique(array_filter([$role, ...$top])));
    $query = implode(' ', $core);
    $encodedQuery = rawurlencode($query);
    $encodedLocation = rawurlencode($location);
    $experienceMap = ['entry' => '2', 'mid' => '3', 'senior' => '4'];
    $linkedInLevel = $experienceMap[$experience] ?? '3';

    return [
        [
            'board' => 'LinkedIn',
            'title' => $role . ' roles matching ' . count($top) . ' portfolio skills',
            'url' => "https://www.linkedin.com/jobs/search/?keywords={$encodedQuery}&location={$encodedLocation}&f_E={$linkedInLevel}",
            'query' => $query,
            'note' => 'Opens LinkedIn with your role, location, and strongest extracted skills.',
        ],
        [
            'board' => 'Indeed',
            'title' => $role . ' openings filtered by your strongest skills',
            'url' => "https://www.indeed.com/jobs?q={$encodedQuery}&l={$encodedLocation}",
            'query' => $query,
            'note' => 'Opens Indeed with the same matching query so you can compare postings quickly.',
        ],
        [
            'board' => 'LinkedIn Boolean',
            'title' => 'Narrow search for high-confidence matches',
            'url' => 'https://www.linkedin.com/jobs/search/?keywords=' . rawurlencode('"' . $role . '" ' . implode(' OR ', array_map(fn($s) => '"' . $s . '"', array_slice($top, 0, 4)))) . "&location={$encodedLocation}",
            'query' => '"' . $role . '" ' . implode(' OR ', array_map(fn($s) => '"' . $s . '"', array_slice($top, 0, 4))),
            'note' => 'Useful when broad searches produce noisy results.',
        ],
    ];
}

function score_job_text(array $portfolioSkills, string $jobText, array $catalog, array $synonyms): array
{
    if ($jobText === '') {
        return ['score' => null, 'matched' => [], 'missing' => []];
    }
    $jobSkills = extract_skills($jobText, $catalog, $synonyms);
    $matched = array_intersect_key($portfolioSkills, $jobSkills);
    $missing = array_diff_key($jobSkills, $portfolioSkills);
    $score = count($jobSkills) === 0 ? 0 : (int)round((count($matched) / count($jobSkills)) * 100);
    return ['score' => $score, 'matched' => $matched, 'missing' => $missing];
}

$portfolioSkills = extract_skills($portfolio, $skillCatalog, $synonyms);
$priorityWords = preg_split('/\s+/', normalize_text($targetRole)) ?: [];
$rankedSkills = rank_skills($portfolioSkills, $priorityWords);
$searches = build_searches($targetRole, $location, $experience, $rankedSkills);
$jobScore = score_job_text($portfolioSkills, $jobText, $skillCatalog, $synonyms);
$grouped = [];
foreach ($rankedSkills as $row) {
    $grouped[$row['group']][] = $row['skill'];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Job Matching AI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="app">
    <section class="workspace">
        <header class="topbar">
            <div>
                <p class="eyebrow">Portfolio-driven search assistant</p>
                <h1>Job Matching AI</h1>
            </div>
            <div class="status">
                <span><?= count($portfolioSkills) ?></span>
                skills found
            </div>
        </header>

        <form method="post" class="panel input-panel">
            <div class="field-row">
                <label>
                    Target role
                    <input name="target_role" value="<?= htmlspecialchars($targetRole) ?>" placeholder="PHP Developer">
                </label>
                <label>
                    Location
                    <input name="location" value="<?= htmlspecialchars($location) ?>" placeholder="Remote, Dhaka, New York">
                </label>
                <label>
                    Experience
                    <select name="experience">
                        <option value="entry" <?= $experience === 'entry' ? 'selected' : '' ?>>Entry</option>
                        <option value="mid" <?= $experience === 'mid' ? 'selected' : '' ?>>Mid</option>
                        <option value="senior" <?= $experience === 'senior' ? 'selected' : '' ?>>Senior</option>
                    </select>
                </label>
            </div>

            <label>
                Portfolio / resume text
                <textarea name="portfolio" rows="12"><?= htmlspecialchars($portfolio) ?></textarea>
            </label>

            <label>
                Optional job description to score
                <textarea name="job_text" rows="6" placeholder="Paste a job post here to compare it against your portfolio."><?= htmlspecialchars($jobText) ?></textarea>
            </label>

            <button type="submit">Analyze and match jobs</button>
        </form>
    </section>

    <aside class="results">
        <section class="panel">
            <div class="panel-heading">
                <h2>Matched Skills</h2>
                <p>Ranked from your portfolio text.</p>
            </div>
            <?php if ($grouped === []): ?>
                <p class="empty">No catalog skills found yet. Add project details, tools, frameworks, and technologies.</p>
            <?php else: ?>
                <div class="skill-groups">
                    <?php foreach ($grouped as $group => $skills): ?>
                        <div>
                            <h3><?= htmlspecialchars($group) ?></h3>
                            <div class="chips">
                                <?php foreach ($skills as $skill): ?>
                                    <span><?= htmlspecialchars($skill) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <h2>Live Job Searches</h2>
                <p>Opens current listings on each board.</p>
            </div>
            <div class="job-list">
                <?php foreach ($searches as $search): ?>
                    <article class="job-card">
                        <div>
                            <span class="board"><?= htmlspecialchars($search['board']) ?></span>
                            <h3><?= htmlspecialchars($search['title']) ?></h3>
                            <p><?= htmlspecialchars($search['note']) ?></p>
                            <code><?= htmlspecialchars($search['query']) ?></code>
                        </div>
                        <a href="<?= htmlspecialchars($search['url']) ?>" target="_blank" rel="noopener">Open jobs</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <h2>Job Fit Score</h2>
                <p>Paste one job post to compare.</p>
            </div>
            <?php if ($jobScore['score'] === null): ?>
                <p class="empty">No job description pasted yet.</p>
            <?php else: ?>
                <div class="score" style="--score: <?= (int)$jobScore['score'] ?>%">
                    <strong><?= (int)$jobScore['score'] ?>%</strong>
                    <span>skill match</span>
                </div>
                <h3>Matched</h3>
                <div class="chips compact">
                    <?php foreach (array_keys($jobScore['matched']) as $skill): ?>
                        <span><?= htmlspecialchars($skill) ?></span>
                    <?php endforeach; ?>
                    <?php if ($jobScore['matched'] === []): ?><em>None found</em><?php endif; ?>
                </div>
                <h3>Missing from portfolio</h3>
                <div class="chips compact missing">
                    <?php foreach (array_keys($jobScore['missing']) as $skill): ?>
                        <span><?= htmlspecialchars($skill) ?></span>
                    <?php endforeach; ?>
                    <?php if ($jobScore['missing'] === []): ?><em>No catalog gaps detected</em><?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </aside>
</main>
</body>
</html>
