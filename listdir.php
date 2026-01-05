<?php
header('Content-Type: text/plain; charset=utf-8');

echo "__DIR__=" . __DIR__ . "\n\n";

$files = scandir(__DIR__);
foreach ($files as $f) {
  echo $f . "\n";
}
