<?php
// Test the actual API endpoint via HTTP
$baseUrl = 'http://localhost/speakon/api/auth';

// Test 1: Login
echo "=== Test Login via HTTP ===\n";
$loginData = json_encode([
    'email' => 'siswa@speakon.id',
    'password' => 'Siswa@123',
]);

$ch = curl_init("$baseUrl/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $httpCode\n";
$decoded = json_decode($response, true);
echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Register
echo "=== Test Register via HTTP ===\n";
$registerData = json_encode([
    'full_name' => 'Test Register User',
    'email' => 'testhttp_' . time() . '@test.com',
    'password' => 'TestPass@123',
    'confirm_password' => 'TestPass@123',
]);

$ch = curl_init("$baseUrl/register");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $registerData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $httpCode\n";
$decoded = json_decode($response, true);
echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
