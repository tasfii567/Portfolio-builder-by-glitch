<?php
/**
 * inc/profile.php — keeps the `profiles` table in shape and loads a profile.
 * Safe to include on every page: it creates the table if missing and adds any
 * new columns to older installs (so you never have to touch phpMyAdmin again).
 */

function ensure_profiles(PDO $pdo): void {
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS profiles (
        user_id    INT PRIMARY KEY,
        image      VARCHAR(255) NOT NULL DEFAULT '',
        phone      VARCHAR(50)  NOT NULL DEFAULT '',
        location   VARCHAR(120) NOT NULL DEFAULT '',
        website    VARCHAR(200) NOT NULL DEFAULT '',
        github     VARCHAR(255) NOT NULL DEFAULT '',
        summary    TEXT,
        skills     TEXT,
        experience TEXT,
        education  TEXT,
        CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
      ) ENGINE=InnoDB
    ");
    // Add any missing columns to tables created by older versions.
    $cols = $pdo->query("SHOW COLUMNS FROM profiles")->fetchAll(PDO::FETCH_COLUMN);
    $need = [
        'image'      => "VARCHAR(255) NOT NULL DEFAULT ''",
        'phone'      => "VARCHAR(50) NOT NULL DEFAULT ''",
        'location'   => "VARCHAR(120) NOT NULL DEFAULT ''",
        'website'    => "VARCHAR(200) NOT NULL DEFAULT ''",
        'github'     => "VARCHAR(255) NOT NULL DEFAULT ''",
        'summary'    => "TEXT",
        'skills'     => "TEXT",
        'experience' => "TEXT",
        'education'  => "TEXT",
    ];
    foreach ($need as $col => $def) {
        if (!in_array($col, $cols, true)) {
            $pdo->exec("ALTER TABLE profiles ADD COLUMN $col $def");
        }
    }
}

function get_profile(PDO $pdo, int $userId): array {
    ensure_profiles($pdo);
    $s = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
    $s->execute([$userId]);
    $p = $s->fetch();
    if (!$p) {
        $pdo->prepare("INSERT INTO profiles (user_id) VALUES (?)")->execute([$userId]);
        $s->execute([$userId]);
        $p = $s->fetch();
    }
    return $p;
}

/** Split a comma/newline list into a clean array of skills. */
function skills_to_array(?string $raw): array {
    return array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', (string)$raw))));
}
