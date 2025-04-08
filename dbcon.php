<?php
require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;

// Inisialisasi Firebase dengan file JSON
$factory = new Factory();
$firebase = $factory
    ->withServiceAccount(__DIR__.'/silagung-firebase-adminsdk-fbsvc-4ecccfb642.json')
    ->withDatabaseUri('https://silagung-default-rtdb.asia-southeast1.firebasedatabase.app');

// Dapatkan instance database
$database = $firebase->createDatabase();

// Fungsi untuk mengambil data
function getData($path) {
    global $database;
    return $database->getReference($path)->getValue();
}

// Fungsi untuk menyimpan data
function saveData($path, $data) {
    global $database;
    $database->getReference($path)->set($data);
}
