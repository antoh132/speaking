<?php
// Quick end-to-end test of login and register
require_once 'api/config/config.php';
require_once 'api/config/db.php';

echo "=== END-TO-END TEST ===\n\n";

$pdo = getDB();

// Test 1: Login with siswa@speakon.id / Siswa@123
echo "1. Login test (siswa@speakon.id / Siswa@123):\n";
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => 'siswa@speakon.id']);
$user = $stmt->fetch();
echo "   password_verify: " . (password_verify('Siswa@123', $user['password_hash']) ? 'PASS' : 'FAIL') . "\n";

// Test 2: Login with admin@speakon.id / Admin@123
echo "2. Login test (admin@speakon.id / Admin@123):\n";
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => 'admin@speakon.id']);
$user = $stmt->fetch();
echo "   password_verify: " . (password_verify('Admin@123', $user['password_hash']) ? 'PASS' : 'FAIL') . "\n";

// Test 3: Login with dosen@speakon.id / Dosen@123
echo "3. Login test (dosen@speakon.id / Dosen@123):\n";
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => 'dosen@speakon.id']);
$user = $stmt->fetch();
echo "   password_verify: " . (password_verify('Dosen@123', $user['password_hash']) ? 'PASS' : 'FAIL') . "\n";

// Test 4: Register new user
echo "\n4. Register test:\n";
try {
    $testId = generateUUID();
    $testHash = password_hash('TestUser@123', PASSWORD_BCRYPT, ['cost' => 12]);
    
    $stmt = $pdo->prepare(
        'INSERT INTO users (id, full_name, email, password_hash, role, is_active, language_pref, created_at, updated_at, created_by)
         VALUES (:id, :full_name, :email, :password_hash, :role, 1, :lang, NOW(), NOW(), NULL)'
    );
    $stmt->execute([
        ':id'            => $testId,
        ':full_name'     => 'Test User E2E',
        ':email'         => 'test_e2e@test.com',
        ':password_hash' => $testHash,
        ':role'          => 'siswa',
        ':lang'          => 'id',
    ]);
    echo "   INSERT user: OK (id: $testId)\n";
    
    // Initialize levels
    require_once 'api/services/LevelService.php';
    LevelService::initializeStudentLevels($testId);
    echo "   Level progress init: OK\n";
    
    // Verify login works
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $testId]);
    $newUser = $stmt->fetch();
    echo "   password_verify for new user: " . (password_verify('TestUser@123', $newUser['password_hash']) ? 'PASS' : 'FAIL') . "\n";
    
    // Cleanup
    $pdo->prepare('DELETE FROM student_level_progress WHERE student_id = :id')->execute([':id' => $testId]);
    $pdo->prepare('DELETE FROM users WHERE id = :id')->execute([':id' => $testId]);
    echo "   Cleanup: OK\n";
} catch (PDOException $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== ALL TESTS COMPLETE ===\n";
