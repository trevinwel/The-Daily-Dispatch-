<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Step 1: Loading config<br>";
require_once __DIR__ . '/includes/config.php';
echo "Step 2: config OK<br>";

require_once __DIR__ . '/includes/Database.php';
echo "Step 3: Database OK<br>";

require_once __DIR__ . '/includes/User.php';
echo "Step 4: User OK<br>";

require_once __DIR__ . '/includes/Auth.php';
echo "Step 5: Auth OK<br>";

echo "All classes loaded!";