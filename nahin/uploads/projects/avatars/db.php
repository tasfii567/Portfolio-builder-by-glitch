<?php
/**
 * db.php — root database bootstrap.
 *
 * view-portfolio.php (and other portfolio pages) require this file with
 * `require __DIR__ . '/db.php';`. To keep a single source of truth we just
 * re-use the existing connection in config/db.php, which defines $pdo.
 */
require __DIR__ . '/config/db.php';
