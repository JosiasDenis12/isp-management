<?php
require_once 'config/config.php';

echo "DEBUG URL FUNCTION:\n";
echo "BASE_URL: " . BASE_URL . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "dirname(SCRIPT_NAME): " . dirname($_SERVER['SCRIPT_NAME']) . "\n";

echo "\nTesting url() function:\n";
echo "url(''): " . url('') . "\n";
echo "url('equipos'): " . url('equipos') . "\n";
echo "url('/equipos'): " . url('/equipos') . "\n";
echo "url('equipos/create'): " . url('equipos/create') . "\n";

echo "\nFull URLs:\n";
echo "http://localhost" . url('equipos') . "\n";
echo "BASE_URL/equipos: " . BASE_URL . "/equipos\n";
?>
