<?php
header('Content-Type: application/json');
require_once 'dbcon.php';

// Ambil data kualitas air dari Firebase
$waterData = getData('waterquality');

// Tambahkan timestamp saat ini jika tidak ada
if (!isset($waterData['last_update'])) {
    $waterData['last_update'] = time() * 1000; // Konversi ke milliseconds
}

// Status untuk setiap parameter
$status = [
    'ph_status' => 'unknown',
    'tds_status' => 'unknown',
    'temperature_status' => 'unknown'
];

// Evaluasi status pH
if (isset($waterData['ph'])) {
    $ph = $waterData['ph'];
    if ($ph < 6.5) {
        $status['ph_status'] = 'terlalu_asam';
    } elseif ($ph > 8.5) {
        $status['ph_status'] = 'terlalu_basa';
    } else {
        $status['ph_status'] = 'normal';
    }
}

// Evaluasi status TDS
if (isset($waterData['tds'])) {
    $tds = $waterData['tds'];
    if ($tds < 100) {
        $status['tds_status'] = 'sangat_baik';
    } elseif ($tds < 500) {
        $status['tds_status'] = 'baik';
    } elseif ($tds < 1000) {
        $status['tds_status'] = 'sedang';
    } else {
        $status['tds_status'] = 'buruk';
    }
}

// Evaluasi status suhu
if (isset($waterData['temperature'])) {
    $temp = $waterData['temperature'];
    if ($temp < 20) {
        $status['temperature_status'] = 'dingin';
    } elseif ($temp < 30) {
        $status['temperature_status'] = 'normal';
    } else {
        $status['temperature_status'] = 'panas';
    }
}

// Gabungkan data dan status
$result = array_merge($waterData, $status);

// Kirim response
echo json_encode($result);
?> 