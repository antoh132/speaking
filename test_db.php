<?php
require 'api/config/db.php';
try {
    $db = getDB();
    echo "Connected successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
