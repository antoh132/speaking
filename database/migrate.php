#!/usr/bin/env php
<?php
/**
 * SpeakOn! — Database Migration Runner
 *
 * Runs schema.sql against the configured MySQL database.
 * Execute from the project root:
 *
 *   php database/migrate.php
 *   php database/migrate.php --seed      # also run seed.sql
 *   php database/migrate.php --fresh     # DROP and recreate database first
 *
 * Requirements: PHP 8.x CLI with PDO + pdo_mysql extension enabled.
 */

require_once __DIR__ . '/../api/config/config.php';

// ── Parse CLI arguments ───────────────────────────────────────────────────────
$args  = array_slice($argv, 1);
$seed  = in_array('--seed',  $args, true);
$fresh = in_array('--fresh', $args, true);

// ── Connect without selecting a database first ────────────────────────────────
$dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT);

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "ERROR: Cannot connect to MySQL: " . $e->getMessage() . "\n");
    exit(1);
}

// ── Optional: drop and recreate database ─────────────────────────────────────
if ($fresh) {
    echo "⚠  --fresh flag detected. Dropping database '" . DB_NAME . "'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");
    echo "   Database dropped.\n";
}

// ── Run schema.sql ────────────────────────────────────────────────────────────
$schemaFile = __DIR__ . '/schema.sql';
if (!file_exists($schemaFile)) {
    fwrite(STDERR, "ERROR: schema.sql not found at {$schemaFile}\n");
    exit(1);
}

echo "Running schema.sql...\n";
runSqlFile($pdo, $schemaFile);
echo "✓  Schema applied successfully.\n";

// ── Optional: run seed.sql ────────────────────────────────────────────────────
if ($seed) {
    $seedFile = __DIR__ . '/seed.sql';
    if (!file_exists($seedFile)) {
        fwrite(STDERR, "WARNING: seed.sql not found at {$seedFile}\n");
    } else {
        echo "Running seed.sql...\n";
        runSqlFile($pdo, $seedFile);
        echo "✓  Seed data inserted.\n";
    }
}

echo "\nMigration complete.\n";
exit(0);

// ── Helper: execute a .sql file statement by statement ────────────────────────
function runSqlFile(PDO $pdo, string $filePath): void
{
    $sql = file_get_contents($filePath);
    if ($sql === false) {
        fwrite(STDERR, "ERROR: Cannot read file: {$filePath}\n");
        exit(1);
    }

    // Split on semicolons, skip empty statements and pure comments
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn(string $s): bool => $s !== '' && !preg_match('/^--/', $s)
    );

    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            // Ignore "table already exists" and "duplicate entry" errors
            // so the migration is idempotent
            $code = (int) $e->getCode();
            if (in_array($code, [1050, 1061, 1062], true)) {
                // 1050 = Table already exists
                // 1061 = Duplicate key name
                // 1062 = Duplicate entry
                continue;
            }
            fwrite(STDERR, "SQL ERROR [{$code}]: " . $e->getMessage() . "\n");
            fwrite(STDERR, "Statement: " . substr($statement, 0, 200) . "\n");
            exit(1);
        }
    }
}
