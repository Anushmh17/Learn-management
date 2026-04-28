<?php
require_once __DIR__ . '/../db.php';

try {
    $pdo->exec("ALTER TABLE students ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER full_name");
    echo "Column 'profile_picture' added successfully to 'students' table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'profile_picture' already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
