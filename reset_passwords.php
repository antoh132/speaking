<?php
require_once 'api/config/config.php';
require_once 'api/config/db.php';

$pdo = getDB();

// Generate new hashes for known passwords
$passwords = [
    'admin@speakon.id'          => 'Admin@123',
    'dosen@speakon.id'          => 'Dosen@123',
    'siswa@speakon.id'          => 'Siswa@123',
    'dewi.lestari@speakon.id'   => 'Siswa@123',
    'rizki.ramadan@speakon.id'  => 'Siswa@123',
    'nur.hidayah@speakon.id'    => 'Siswa@123',
    'alya@gmail.com'            => 'Siswa@123',
];

$stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE email = :email');

foreach ($passwords as $email => $pwd) {
    $hash = password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt->execute([':hash' => $hash, ':email' => $email]);
    echo "Updated $email with password '$pwd'\n";
}

echo "\nDone! Now verifying...\n\n";

// Verify
foreach ($passwords as $email => $pwd) {
    $check = $pdo->prepare('SELECT password_hash FROM users WHERE email = :email LIMIT 1');
    $check->execute([':email' => $email]);
    $user = $check->fetch();
    $ok = password_verify($pwd, $user['password_hash']);
    echo "$email => " . ($ok ? 'PASS' : 'FAIL') . "\n";
}
