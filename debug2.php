<?php
echo "<pre>";
echo "Current dir: " . __DIR__ . "\n";
echo "Backend src path: " . __DIR__ . '/backend/src/' . "\n";
echo "Database.php exists: " . (file_exists(__DIR__ . '/backend/src/Config/Database.php') ? 'YES' : 'NO') . "\n";
echo "Router.php exists: " . (file_exists(__DIR__ . '/backend/src/Router.php') ? 'YES' : 'NO') . "\n";
echo "Config database.php exists: " . (file_exists(__DIR__ . '/backend/config/database.php') ? 'YES' : 'NO') . "\n";

$prefix = 'Innow\\';
$baseDir = __DIR__ . '/../backend/src/';
$class = 'Innow\\Config\\Database';
$relativeClass = substr($class, strlen($prefix));
$file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
echo "Resolved file: " . $file . "\n";
echo "File exists: " . (file_exists($file) ? 'YES' : 'NO') . "\n";
echo "</pre>";
