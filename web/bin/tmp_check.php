<?php
// Quick runtime check for autoload and PHPUnit availability
echo "PHP CLI: " . PHP_BINARY . "\n";
$autoload = __DIR__ . '/vendor/autoload.php';
echo "Checking autoload: $autoload\n";
echo file_exists($autoload) ? "autoload: exists\n" : "autoload: missing\n";
if (file_exists($autoload)) {
    require $autoload;
    echo "PHPUnit TestCase class available: " . (class_exists('PHPUnit\\Framework\\TestCase') ? "YES" : "NO") . "\n";
}
