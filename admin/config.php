<?php
require_once __DIR__ . '/../nahin/config/db.php';

function admin_table_exists(PDO $pdo, string $table): bool
{
    static $cache = [];

    if (!array_key_exists($table, $cache)) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?"
        );
        $stmt->execute([$table]);
        $cache[$table] = (bool) $stmt->fetchColumn();
    }

    return $cache[$table];
}

function admin_columns(PDO $pdo, string $table): array
{
    static $cache = [];

    if (!array_key_exists($table, $cache)) {
        if (!admin_table_exists($pdo, $table)) {
            $cache[$table] = [];
            return $cache[$table];
        }

        $stmt = $pdo->prepare(
            "SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?"
        );
        $stmt->execute([$table]);
        $cache[$table] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    return $cache[$table];
}

function admin_has_column(PDO $pdo, string $table, string $column): bool
{
    return in_array($column, admin_columns($pdo, $table), true);
}

function admin_count(PDO $pdo, string $table, ?string $where = null): int
{
    if (!admin_table_exists($pdo, $table)) {
        return 0;
    }

    try {
        $sql = "SELECT COUNT(*) FROM `$table`" . ($where ? " WHERE $where" : '');
        return (int) $pdo->query($sql)->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

function admin_fetch_all(PDO $pdo, string $sql): array
{
    try {
        return $pdo->query($sql)->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
?>
