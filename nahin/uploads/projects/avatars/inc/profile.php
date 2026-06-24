<?php
/**
 * inc/profile.php — gather a user's public profile data for view-portfolio.php.
 *
 * get_profile() returns:
 *   image, location, summary, github, website  (strings)
 *   skills                                      (comma string -> skills_to_array)
 *   experience, education                       (arrays of rows for the template)
 */

function get_profile(PDO $pdo, int $userId): array
{
    // Core profile row.
    $stmt = $pdo->prepare('SELECT avatar, title, bio, location, phone FROM profiles WHERE user_id = ?');
    $stmt->execute([$userId]);
    $p = $stmt->fetch() ?: [];

    // Social links -> pick out github + a website/portfolio link.
    $github = '';
    $website = '';
    $sl = $pdo->prepare('SELECT platform, url FROM social_links WHERE user_id = ?');
    $sl->execute([$userId]);
    foreach ($sl->fetchAll() as $row) {
        $plat = strtolower($row['platform'] ?? '');
        $url  = trim($row['url'] ?? '');
        if ($url === '') {
            continue;
        }
        if ($github === '' && strpos($plat, 'github') !== false) {
            $github = $url;
        } elseif ($website === '' && (strpos($plat, 'web') !== false || strpos($plat, 'portfolio') !== false || strpos($plat, 'site') !== false)) {
            $website = $url;
        }
    }

    // Skills -> "a, b, c"
    $sk = $pdo->prepare('SELECT skill_name FROM skills WHERE user_id = ? ORDER BY id');
    $sk->execute([$userId]);
    $skills = implode(', ', array_filter($sk->fetchAll(PDO::FETCH_COLUMN)));

    // Experience -> readable rows.
    $ex = $pdo->prepare('SELECT company, position, start_date, end_date, currently_working, description
                         FROM experience WHERE user_id = ? ORDER BY COALESCE(start_date, "1900-01-01") DESC, id DESC');
    $ex->execute([$userId]);
    $experience = [];
    foreach ($ex->fetchAll() as $e) {
        $end = $e['currently_working'] ? 'Present' : ($e['end_date'] ?: '');
        $experience[] = [
            'title'   => trim(($e['position'] ?: '') . (($e['company'] ?? '') !== '' ? ' · ' . $e['company'] : '')),
            'period'  => trim(($e['start_date'] ?: '') . ($end ? ' – ' . $end : ''), ' –'),
            'detail'  => $e['description'] ?: '',
        ];
    }

    // Education -> readable rows.
    $ed = $pdo->prepare('SELECT institution, degree, field_of_study, start_year, end_year, gpa
                         FROM education WHERE user_id = ? ORDER BY COALESCE(end_year, 0) DESC, id DESC');
    $ed->execute([$userId]);
    $education = [];
    foreach ($ed->fetchAll() as $d) {
        $deg = trim(($d['degree'] ?: '') . (($d['field_of_study'] ?? '') !== '' ? ' in ' . $d['field_of_study'] : ''));
        $education[] = [
            'title'  => $deg !== '' ? $deg : ($d['institution'] ?: 'Education'),
            'period' => trim(($d['start_year'] ?: '') . ($d['end_year'] ? ' – ' . $d['end_year'] : ''), ' –'),
            'detail' => trim(($d['institution'] ?: '') . (($d['gpa'] ?? '') !== '' ? ' · GPA ' . $d['gpa'] : '')),
        ];
    }

    return [
        'image'      => $p['avatar']   ?? '',
        'location'   => $p['location'] ?? '',
        'summary'    => $p['bio']      ?? '',
        'github'     => $github,
        'website'    => $website,
        'skills'     => $skills,
        'experience' => $experience,
        'education'  => $education,
    ];
}

/**
 * Normalise a skills value (comma / newline / semicolon separated, or array)
 * into a clean array of skill strings.
 */
function skills_to_array($skills): array
{
    if (is_array($skills)) {
        return array_values(array_filter(array_map('trim', $skills), fn($s) => $s !== ''));
    }
    $parts = preg_split('/[,\n;]+/', (string) $skills) ?: [];
    return array_values(array_filter(array_map('trim', $parts), fn($s) => $s !== ''));
}
