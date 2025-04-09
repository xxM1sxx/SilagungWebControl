<?php
require_once 'dbcon.php';

// Reset flag sinkronisasi
saveData("silagung-controller/commands/sync_schedules", false);

// Kirim respons
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?> 