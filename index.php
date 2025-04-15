<?php
require_once 'dbcon.php';

// Fungsi untuk mengupdate status relay
if (isset($_POST['update_relay'])) {
    $relay_number = $_POST['relay_number'];
    $status = isset($_POST['status']) ? $_POST['status'] : 'off';
    
    // Update status relay
    saveData("silagung-controller/relay/relay{$relay_number}", $status == 'on');
    
    // Jika mode tidak manual, ubah ke mode manual
    $current_mode = getData("silagung-controller/status/activeMode") ?? "Manual";
    if ($current_mode != "Manual") {
        saveData("silagung-controller/status/activeMode", "Manual");
        saveData("silagung-controller/commands/mode", "manual");
    }
}

// Fungsi untuk mengupdate VFD
if (isset($_POST['update_vfd'])) {
    $vfd_status = isset($_POST['vfd_status']) ? 'on' : 'off';
    // Tidak perlu lagi mengatur frekuensi karena VFD dikendalikan oleh relay 6 saja
    saveData("silagung-controller/commands/vfd", $vfd_status == 'on');
    
    // Update relay 6 sesuai status VFD
    saveData("silagung-controller/relay/relay6", $vfd_status == 'on');
}

// Fungsi untuk mengatur mode relay
if (isset($_POST['set_mode'])) {
    $mode = $_POST['mode'];
    
    // Kirim perintah mode ke ESP32
    saveData("silagung-controller/commands/mode", $mode);
    
    switch ($mode) {
        case 'isibak':
            // Mode isibak
            saveData("silagung-controller/relay/relay1", true);
            saveData("silagung-controller/relay/relay2", true);
            saveData("silagung-controller/relay/relay3", false);
            saveData("silagung-controller/relay/relay4", true);
            saveData("silagung-controller/relay/relay5", false);
            saveData("silagung-controller/relay/relay6", true);  // Relay 6 ON untuk VFD
            
            // Aktifkan VFD juga (relay 6 sudah diaktifkan di atas)
            saveData("silagung-controller/commands/vfd", true);
            
            // Update mode aktif
            saveData("silagung-controller/status/activeMode", "Isi Bak");
            break;
            
        case 'mixing':
            // Mode mixing
            saveData("silagung-controller/relay/relay1", false);
            saveData("silagung-controller/relay/relay2", false);
            saveData("silagung-controller/relay/relay3", true);
            saveData("silagung-controller/relay/relay4", true);
            saveData("silagung-controller/relay/relay5", false);
            saveData("silagung-controller/relay/relay6", true);  // Relay 6 ON untuk VFD
            
            // Aktifkan VFD juga (relay 6 sudah diaktifkan di atas)
            saveData("silagung-controller/commands/vfd", true);
            
            // Update mode aktif
            saveData("silagung-controller/status/activeMode", "Mixing");
            break;
            
        case 'supply':
            // Mode supply
            saveData("silagung-controller/relay/relay1", true);
            saveData("silagung-controller/relay/relay2", false);
            saveData("silagung-controller/relay/relay3", false);
            saveData("silagung-controller/relay/relay4", false);
            saveData("silagung-controller/relay/relay5", true);
            saveData("silagung-controller/relay/relay6", true);  // Relay 6 ON untuk VFD
            
            // Aktifkan VFD juga (relay 6 sudah diaktifkan di atas)
            saveData("silagung-controller/commands/vfd", true);
            
            // Update mode aktif
            saveData("silagung-controller/status/activeMode", "Supply");
            break;

        case 'all_off':
            // Matikan semua relay
            for ($i = 1; $i <= 6; $i++) {
                saveData("silagung-controller/relay/relay{$i}", false);
            }
            
            // Matikan VFD juga (relay 6 sudah dimatikan di atas)
            saveData("silagung-controller/commands/vfd", false);
            
            // Update mode aktif
            saveData("silagung-controller/status/activeMode", "Semua Mati");
            break;
            
        case 'manual':
            // Tetap di status yang ada, hanya ubah mode ke manual
            saveData("silagung-controller/status/activeMode", "Manual");
            break;
    }
}

// Fungsi untuk menambah jadwal
if (isset($_POST['add_schedule'])) {
    $schedule_id = "schedule_main"; // ID tetap untuk jadwal tunggal
    $schedule_name = $_POST['schedule_name'];
    
    // Ambil jadwal waktu dari form
    $schedule_times = isset($_POST['schedule_times']) ? $_POST['schedule_times'] : [];
    
    // Filter waktu kosong
    $schedule_times = array_filter($schedule_times, function($time) {
        return !empty($time);
    });
    
    $schedule_days = isset($_POST['schedule_days']) ? $_POST['schedule_days'] : [];
    
    // Ambil sequence mode dari form
    $sequence = [];
    if (isset($_POST['mode_type']) && is_array($_POST['mode_type']) &&
        isset($_POST['mode_duration']) && is_array($_POST['mode_duration'])) {
        
        $mode_types = $_POST['mode_type'];
        $mode_durations = $_POST['mode_duration'];
        
        for ($i = 0; $i < count($mode_types); $i++) {
            if (!empty($mode_types[$i]) && isset($mode_durations[$i]) && $mode_durations[$i] > 0) {
                $sequence[] = [
                    'mode' => $mode_types[$i],
                    'duration' => (int)$mode_durations[$i]
                ];
            }
        }
    }
    
    $schedule_data = [
        'id' => $schedule_id,
        'name' => $schedule_name,
        'times' => $schedule_times,
        'days' => $schedule_days,
        'sequence' => $sequence,
        'active' => true
    ];
    
    // Buat array baru berisi hanya satu jadwal
    $schedules = [];
    $schedules[$schedule_id] = $schedule_data;
    
    // Simpan ke database, akan menimpa semua jadwal yang ada
    saveData("silagung-controller/schedules", $schedules);
    
    // TAMBAHKAN INI: Update versi jadwal
    $current_version = getData("silagung-controller/schedule_version") ?? 0;
    saveData("silagung-controller/schedule_version", $current_version + 1);
}

// Fungsi untuk menghapus jadwal
if (isset($_POST['delete_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    
    // Ambil jadwal yang sudah ada
    $schedules = getData("silagung-controller/schedules") ?? [];
    
    // Hapus jadwal
    if (isset($schedules[$schedule_id])) {
        unset($schedules[$schedule_id]);
    }
    
    // Simpan kembali ke database
    saveData("silagung-controller/schedules", $schedules);
}

// Fungsi untuk mengaktifkan/menonaktifkan jadwal
if (isset($_POST['toggle_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    
    // Ambil jadwal yang sudah ada
    $schedules = getData("silagung-controller/schedules") ?? [];
    
    // Toggle status aktif
    if (isset($schedules[$schedule_id])) {
        $schedules[$schedule_id]['active'] = !$schedules[$schedule_id]['active'];
    }
    
    // Simpan kembali ke database
    saveData("silagung-controller/schedules", $schedules);
}

// Ambil status terkini
$relay_status = [];
for ($i = 1; $i <= 6; $i++) {
    $relay_status[$i] = getData("silagung-controller/relay/relay{$i}");
}

$vfd_status = getData("silagung-controller/commands/vfd");
$vfd_frequency = getData("silagung-controller/commands/frequency") ?? 50;

// Ambil mode aktif
$active_mode = getData("silagung-controller/status/activeMode") ?? "Manual";

// Ambil jadwal
$schedules = getData("silagung-controller/schedules") ?? [];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silagung Controller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <style>
        body {
            background-color: #f5f5f5;
            padding-top: 20px;
        }
        
        .card {
            margin-bottom: 20px;
            border-radius: 6px;
        }
        
        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.25em;
        }
        
        .mode-active {
            background-color: #e9f5ff;
            border-color: #0d6efd;
        }
        
        .mode-button {
            margin-bottom: 8px;
        }
        
        .status-badge {
            width: 10px;
            height: 10px;
            display: inline-block;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-on {
            background-color: #198754;
        }
        
        .status-off {
            background-color: #dc3545;
        }
        
        .schedule-item {
            border-bottom: 1px solid #eee;
            padding: 8px 0;
        }
        
        .schedule-item:last-child {
            border-bottom: none;
        }
        
        .nav-tabs .nav-link {
            color: #666;
        }
        
        .nav-tabs .nav-link.active {
            font-weight: 500;
            color: #0d6efd;
        }
        
        .sequence-step {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            border: 1px solid #e9e9e9;
        }
        
        .badge-step {
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
            margin-right: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Silagung Controller</h2>
        
        <!-- Tab Menu -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" id="control-tab" data-bs-toggle="tab" href="#control">Kontrol Manual</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="schedule-tab" data-bs-toggle="tab" href="#schedule">Penjadwalan</a>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- Kontrol Manual Tab -->
            <div class="tab-pane fade show active" id="control">
                <!-- Status Info Bar -->
        <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="me-3 fw-bold">VFD: 
                                    <span class="status-badge <?php echo $vfd_status ? 'status-on' : 'status-off'; ?>"></span>
                                    <?php echo $vfd_status ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                                <span class="me-3">(Dikendalikan oleh Relay 6)</span>
                            </div>
                            <div class="badge bg-primary">
                                Mode: <?php echo $active_mode; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VFD Control -->
                <div class="card">
            <div class="card-header">
                        Kontrol VFD
            </div>
            <div class="card-body">
                <div class="alert alert-info alert-sm py-2 mb-3">
                    VFD dikendalikan melalui Relay 6. Pengaturan ini akan otomatis mengontrol Relay 6. Pengaturan kecepatan VFD dilakukan secara manual di panel kelistrikan.
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="update_vfd" value="1">
                    <div class="row align-items-center">
                                <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="vfd_status" 
                                       id="vfdSwitch" <?php echo $vfd_status ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="vfdSwitch">
                                            VFD <?php echo $vfd_status ? 'Aktif' : 'Nonaktif'; ?>
                                        </label>
                            </div>
                        </div>
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-primary btn-sm">Update VFD</button>
                                </div>
                    </div>
                </form>
            </div>
        </div>

                <!-- Mode Control -->
                <div class="card">
            <div class="card-header">
                        Mode Kontrol Relay
            </div>
            <div class="card-body">
                        <div class="alert alert-info alert-sm py-2 mb-3">
                            Mode Isi Bak, Mixing, dan Supply akan menyalakan VFD otomatis melalui Relay 6. Mode Manual memungkinkan kontrol relay satu per satu, kecuali Relay 6 yang dikontrol melalui menu VFD.
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="set_mode" value="1">
                            <div class="row">
                                <div class="col-6 col-md-3">
                                    <button type="submit" name="mode" value="isibak" 
                                        class="btn btn-outline-primary w-100 mode-button <?php echo ($active_mode == 'Isi Bak') ? 'mode-active' : ''; ?>">
                                        Isi Bak
                                    </button>
                                </div>
                                <div class="col-6 col-md-3">
                                    <button type="submit" name="mode" value="mixing" 
                                        class="btn btn-outline-success w-100 mode-button <?php echo ($active_mode == 'Mixing') ? 'mode-active' : ''; ?>">
                                        Mixing
                                    </button>
                        </div>
                                <div class="col-6 col-md-3">
                                    <button type="submit" name="mode" value="supply" 
                                        class="btn btn-outline-info w-100 mode-button <?php echo ($active_mode == 'Supply') ? 'mode-active' : ''; ?>">
                                        Supply
                                    </button>
                        </div>
                                <div class="col-6 col-md-3">
                                    <button type="submit" name="mode" value="all_off" 
                                        class="btn btn-outline-danger w-100 mode-button <?php echo ($active_mode == 'Semua Mati') ? 'mode-active' : ''; ?>">
                                        Matikan Semua
                                    </button>
                        </div>
                                <div class="col-12 mt-2">
                                    <button type="submit" name="mode" value="manual" 
                                        class="btn btn-outline-secondary w-100 <?php echo ($active_mode == 'Manual') ? 'mode-active' : ''; ?>">
                                        Mode Manual
                                    </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

                <!-- Relay Control -->
                <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Kontrol Relay</span>
                <?php if ($active_mode != 'Manual'): ?>
                        <span class="badge bg-warning">Mode: <?php echo $active_mode; ?></span>
                        <?php else: ?>
                        <span class="badge bg-success">Mode Manual</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($active_mode != 'Manual'): ?>
                        
                        <div class="alert alert-warning py-2 mb-3">
                            Relay dikontrol oleh mode <?php echo $active_mode; ?>. Aktifkan mode manual untuk mengontrol relay.
                        </div>
                        
                        <form method="POST" action="" class="mb-3">
                            <input type="hidden" name="set_manual_mode" value="1">
                            <button type="submit" class="btn btn-primary btn-sm">Aktifkan Mode Manual</button>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Relay</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <tr>
                                        <td>Relay <?php echo $i; ?></td>
                                        <td>
                                            <span class="badge <?php echo $relay_status[$i] ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $relay_status[$i] ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                    <!-- Tampilan khusus untuk Relay 6 (VFD) -->
                                    <tr class="table-light">
                                        <td>Relay 6 (VFD)</td>
                                        <td>
                                            <span class="badge <?php echo $relay_status[6] ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $relay_status[6] ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                            <small class="text-muted"> - Dikontrol oleh menu VFD</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                </div>
                        
                        <?php else: ?>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Relay</th>
                                        <th>Status</th>
                                        <th width="100">Kontrol</th>
                                    </tr>
                                </thead>
                                <tbody>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <tr>
                                        <td>Relay <?php echo $i; ?></td>
                                        <td>
                                            <span class="badge <?php echo $relay_status[$i] ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $relay_status[$i] ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" action="">
                            <input type="hidden" name="update_relay" value="1">
                            <input type="hidden" name="relay_number" value="<?php echo $i; ?>">
                                                <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       name="status" value="on"
                                       <?php echo $relay_status[$i] ? 'checked' : ''; ?>
                                       onchange="this.form.submit()">
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Tab Penjadwalan -->
            <div class="tab-pane fade" id="schedule">
                <div class="card">
                    <div class="card-header">
                        Tambah Jadwal Baru
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="scheduleForm">
                            <input type="hidden" name="add_schedule" value="1">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="schedule_name" class="form-label">Nama Jadwal</label>
                                    <input type="text" class="form-control" id="schedule_name" name="schedule_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jadwal Waktu</label>
                                    <div id="time-container">
                                        <div class="input-group mb-2">
                                            <input type="time" class="form-control" name="schedule_times[]" required>
                                            <button class="btn btn-outline-secondary add-time-btn" type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Hari</label>
                                    <div class="d-flex flex-wrap">
                                        <?php
                                        $days = [
                                            'monday' => 'Senin',
                                            'tuesday' => 'Selasa',
                                            'wednesday' => 'Rabu',
                                            'thursday' => 'Kamis',
                                            'friday' => 'Jumat',
                                            'saturday' => 'Sabtu',
                                            'sunday' => 'Minggu'
                                        ];
                                        foreach ($days as $day_value => $day_name): ?>
                                            <div class="form-check me-3 mb-2">
                                                <input class="form-check-input" type="checkbox" name="schedule_days[]" id="day_<?php echo $day_value; ?>" value="<?php echo $day_value; ?>">
                                                <label class="form-check-label" for="day_<?php echo $day_value; ?>">
                                                    <?php echo $day_name; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <h6 class="mb-3">Urutan Mode</h6>
                            <div id="sequence-container">
                                <div class="sequence-step" id="step-1">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Mode</label>
                                            <select class="form-select" name="mode_type[]" required>
                                                <option value="">Pilih Mode</option>
                                                <option value="isibak">Isi Bak</option>
                                                <option value="mixing">Mixing</option>
                                                <option value="supply">Supply</option>
                                                <option value="all_off">Matikan Semua</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Durasi (menit)</label>
                                            <input type="number" class="form-control" name="mode_duration[]" min="1" max="1440" value="10" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex mt-2 mb-4">
                                <button type="button" id="add-step" class="btn btn-sm btn-success me-2">
                                    + Tambah Mode
                                </button>
                                <button type="button" id="remove-step" class="btn btn-sm btn-danger">
                                    - Hapus Mode Terakhir
                                </button>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Tambah Jadwal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        Daftar Jadwal
                    </div>
                    <div class="card-body">
                        <?php if (empty($schedules)): ?>
                            <div class="alert alert-info">
                                Belum ada jadwal yang ditambahkan.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-3">
                                Saat ini hanya bisa menyimpan 1 jadwal aktif. Menambahkan jadwal baru akan menggantikan jadwal yang sudah ada.
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Waktu</th>
                                            <th>Hari</th>
                                            <th>Urutan Mode</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($schedules as $schedule): ?>
                                            <tr>
                                                <td><?php echo $schedule['name']; ?></td>
                                                <td>
                                                    <?php 
                                                    if (isset($schedule['times']) && is_array($schedule['times'])) {
                                                        echo implode('<br>', $schedule['times']);
                                                    } else if (isset($schedule['time'])) {
                                                        // Untuk kompatibilitas dengan data lama
                                                        echo $schedule['time'];
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $day_names_short = [
                                                        'monday' => 'Sen',
                                                        'tuesday' => 'Sel',
                                                        'wednesday' => 'Rab',
                                                        'thursday' => 'Kam',
                                                        'friday' => 'Jum',
                                                        'saturday' => 'Sab',
                                                        'sunday' => 'Min'
                                                    ];
                                                    
                                                    $selected_days = [];
                                                    if (isset($schedule['days']) && is_array($schedule['days'])) {
                                                        foreach ($schedule['days'] as $day) {
                                                            $selected_days[] = $day_names_short[$day] ?? $day;
                                                        }
                                                    }
                                                    
                                                    echo empty($selected_days) ? '-' : implode(', ', $selected_days);
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $mode_names = [
                                                        'isibak' => 'Isi Bak',
                                                        'mixing' => 'Mixing',
                                                        'supply' => 'Supply',
                                                        'all_off' => 'Matikan Semua'
                                                    ];
                                                    
                                                    $mode_colors = [
                                                        'isibak' => 'primary',
                                                        'mixing' => 'success',
                                                        'supply' => 'info',
                                                        'all_off' => 'danger'
                                                    ];
                                                    
                                                    if (isset($schedule['sequence']) && is_array($schedule['sequence']) && !empty($schedule['sequence'])) {
                                                        foreach ($schedule['sequence'] as $step) {
                                                            $mode_name = isset($mode_names[$step['mode']]) ? $mode_names[$step['mode']] : $step['mode'];
                                                            $mode_color = isset($mode_colors[$step['mode']]) ? $mode_colors[$step['mode']] : 'secondary';
                                                            echo '<span class="badge bg-' . $mode_color . ' badge-step">' . $mode_name . ' (' . $step['duration'] . ' mnt)</span>';
                                                        }
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="toggle_schedule" value="1">
                                                        <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-<?php echo $schedule['active'] ? 'success' : 'secondary'; ?>">
                                                            <?php echo $schedule['active'] ? 'Aktif' : 'Nonaktif'; ?>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="delete_schedule" value="1">
                                                        <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <small>
                        <strong>Catatan:</strong> Jadwal ini akan diproses oleh sistem kontrol sesuai dengan waktu yang ditentukan.
                        Pastikan perangkat ESP32 terhubung ke internet dan waktu sistem sudah sinkron dengan waktu server.
                    </small>
                </div>
            </div>
        </div>
        
        <div class="text-center text-muted mt-3 mb-5">
            <small>&copy; <?php echo date('Y'); ?> Silagung Controller</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script>
        // Toggle text untuk VFD switch
        document.getElementById('vfdSwitch').addEventListener('change', function(e) {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.innerHTML = 'VFD Aktif';
            } else {
                label.innerHTML = 'VFD Nonaktif';
            }
        });

        // Script untuk menambahkan dan menghapus langkah mode
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('sequence-container');
            const addBtn = document.getElementById('add-step');
            const removeBtn = document.getElementById('remove-step');
            
            let stepCount = 1;
            
            addBtn.addEventListener('click', function() {
                stepCount++;
                const newStep = document.createElement('div');
                newStep.className = 'sequence-step';
                newStep.id = 'step-' + stepCount;
                
                newStep.innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Mode</label>
                            <select class="form-select" name="mode_type[]" required>
                                <option value="">Pilih Mode</option>
                                <option value="isibak">Isi Bak</option>
                                <option value="mixing">Mixing</option>
                                <option value="supply">Supply</option>
                                <option value="all_off">Matikan Semua</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Durasi (menit)</label>
                            <input type="number" class="form-control" name="mode_duration[]" min="1" max="1440" value="10" required>
                        </div>
                    </div>
                `;
                
                container.appendChild(newStep);
                
                if (stepCount > 1) {
                    removeBtn.disabled = false;
                }
            });
            
            removeBtn.addEventListener('click', function() {
                if (stepCount > 1) {
                    const lastStep = document.getElementById('step-' + stepCount);
                    container.removeChild(lastStep);
                    stepCount--;
                    
                    if (stepCount === 1) {
                        removeBtn.disabled = true;
                    }
                }
            });
            
            // Inisialisasi status tombol hapus
            if (stepCount <= 1) {
                removeBtn.disabled = true;
            }
        });

        // Script untuk mengelola input waktu 
        document.addEventListener('DOMContentLoaded', function() {
            const timeContainer = document.getElementById('time-container');
            
            // Handler untuk tombol tambah waktu
            timeContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('add-time-btn')) {
                    const currentGroup = e.target.closest('.input-group');
                    
                    // Buat elemen input waktu baru
                    const newGroup = document.createElement('div');
                    newGroup.className = 'input-group mb-2';
                    newGroup.innerHTML = `
                        <input type="time" class="form-control" name="schedule_times[]">
                        <button class="btn btn-outline-secondary add-time-btn" type="button">+</button>
                        <button class="btn btn-outline-danger remove-time-btn" type="button">-</button>
                    `;
                    
                    // Sisipkan elemen baru setelah elemen saat ini
                    currentGroup.after(newGroup);
                } else if (e.target.classList.contains('remove-time-btn')) {
                    const currentGroup = e.target.closest('.input-group');
                    
                    // Hapus grup input jika bukan yang terakhir
                    if (timeContainer.querySelectorAll('.input-group').length > 1) {
                        currentGroup.remove();
                    }
                }
            });
        });
    </script>
</body>
</html> 