<?php
header('Content-Type: text/plain; charset=utf-8');
echo "PING OK\n";
echo "SCRIPT_FILENAME=" . ($_SERVER['SCRIPT_FILENAME'] ?? '') . "\n";
echo "DOCUMENT_ROOT=" . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo "__DIR__=" . __DIR__ . "\n";
