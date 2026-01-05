<?php
$pw = $argv[1] ?? 'Admin123!';
echo password_hash($pw, PASSWORD_DEFAULT) . PHP_EOL;
