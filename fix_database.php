<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec('CREATE DATABASE IF NOT EXISTS laravel_postage_system');
    echo "Database laravel_postage_system created successfully\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
