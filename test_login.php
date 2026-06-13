<?php
require_once 'api/config/config.php';
require_once 'api/config/db.php';

$pdo = getDB();

// 1. Check what password hashes look like
$stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role FROM users LIMIT 10');
$stmt->execute();
$users = $stmt->fetchAll();
echo "=== Users in DB ===\n";
foreach ($users as $u) {
    echo "  {$u['email']} ({$u['role']}) => hash: " . substr($u['password_hash'], 0, 30) . "...\n";
}

// 2. Test passwords
$testPasswords = ['password', '12345678', 'admin123', 'Password123', 'Hardiansyah_23', 'secret123', 'admin', 'speakon123', 'siswa123', 'dosen123'];

echo "\n=== Testing passwords for siswa@speakon.id ===\n";
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => 'siswa@speakon.id']);
$user = $stmt->fetch();
if ($user) {
    foreach ($testPasswords as $pwd) {
        $result = password_verify($pwd, $user['password_hash']);
        echo "  '$pwd' => " . ($result ? 'MATCH!' : 'no') . "\n";
    }
}

echo "\n=== Testing passwords for admin@speakon.id ===\n";
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => 'admin@speakon.id']);
$user = $stmt->fetch();
if ($user) {
    foreach ($testPasswords as $pwd) {
        $result = password_verify($pwd, $user['password_hash']);
        echo "  '$pwd' => " . ($result ? 'MATCH!' : 'no') . "\n";
    }
}

echo "\n=== Testing passwords for dosen@speakon.id ===\n";
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => 'dosen@speakon.id']);
$user = $stmt->fetch();
if ($user) {
    foreach ($testPasswords as $pwd) {
        $result = password_verify($pwd, $user['password_hash']);
        echo "  '$pwd' => " . ($result ? 'MATCH!' : 'no') . "\n";
    }
}

// 3. Check tables
$stmt = $pdo->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "\n=== Tables ===\n";
foreach ($tables as $t) {
    echo "  $t\n";
}

// 4. Test register flow - try inserting a test user
echo "\n=== Register test ===\n";
try {
    $testId = '99999999-test-test-test-000000000001';
    $testHash = password_hash('test1234', PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Check if test user already exists
    $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $check->execute([':email' => 'test_register@test.com']);
    $existing = $check->fetch();
    
    if ($existing) {
        $pdo->prepare('DELETE FROM student_level_progress WHERE student_id = :id')->execute([':id' => $existing['id']]);
        $pdo->prepare('DELETE FROM users WHERE id = :id')->execute([':id' => $existing['id']]);
        echo "  Cleaned up previous test user\n";
    }
    
    $stmt = $pdo->prepare(
        'INSERT INTO users (id, full_name, email, password_hash, role, is_active, language_pref, created_at, updated_at, created_by)
         VALUES (:id, :full_name, :email, :password_hash, :role, 1, :lang, NOW(), NOW(), NULL)'
    );
    $stmt->execute([
        ':id'            => $testId,
        ':full_name'     => 'Test Register',
        ':email'         => 'test_register@test.com',
        ':password_hash' => $testHash,
        ':role'          => 'siswa',
        ':lang'          => 'id',
    ]);
    echo "  INSERT user: OK\n";
    
    // Try initializing level progress
    $levelStmt = $pdo->query('SELECT id FROM levels ORDER BY order_index');
    $levels = $levelStmt->fetchAll();
    echo "  Levels found: " . count($levels) . "\n";
    
    foreach ($levels as $i => $level) {
        $status = ($i === 0) ? 'active' : 'locked';
        $progressId = sprintf('99999999-test-test-test-%012d', $i + 100);
        $progStmt = $pdo->prepare(
            'INSERT INTO student_level_progress (id, student_id, level_id, status, unlocked_at)
             VALUES (:id, :student_id, :level_id, :status, :unlocked_at)'
        );
        $progStmt->execute([
            ':id'          => $progressId,
            ':student_id'  => $testId,
            ':level_id'    => $level['id'],
            ':status'      => $status,
            ':unlocked_at' => ($i === 0) ? date('Y-m-d H:i:s') : null,
        ]);
    }
    echo "  INSERT level progress: OK\n";
    
    // Clean up test data
    $pdo->prepare('DELETE FROM student_level_progress WHERE student_id = :id')->execute([':id' => $testId]);
    $pdo->prepare('DELETE FROM users WHERE id = :id')->execute([':id' => $testId]);
    echo "  Cleanup: OK\n";
    echo "  Register flow works correctly!\n";
    
} catch (PDOException $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}
