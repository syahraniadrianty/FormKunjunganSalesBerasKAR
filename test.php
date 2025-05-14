<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('kunjungansalesberaskar-ae464a920fa5.json');

echo "✅ File JSON & autoload berhasil!";
