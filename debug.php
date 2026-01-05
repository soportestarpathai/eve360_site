<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "PHP OK<br>";
echo "PHP version: " . PHP_VERSION . "<br>";
echo "Loaded ini: " . php_ini_loaded_file() . "<br>";

echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";
?>