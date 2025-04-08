<?php
require_once 'dbcon.php';

// Fungsi untuk mengupdate status relay
if (isset($_POST['update_relay'])) {
    $relay_number = $_POST['relay_number'];
    $status = isset($_POST['status']) ? $_POST['status'] : 'off';
    saveData("silagung-controller/relay/relay{$relay_number}", $status == 'on');
}

// Fungsi untuk mengupdate VFD
if (isset($_POST['update_vfd'])) {
    $vfd_status = $_POST['vfd_status'];
    $frequency = $_POST['frequency'];
    saveData("silagung-controller/commands/vfd", $vfd_status == 'on');
    saveData("silagung-controller/commands/frequency", floatval($frequency));
}

// Fungsi untuk mengatur mode relay
if (isset($_POST['set_mode'])) {
    $mode = $_POST['mode'];
    
    switch ($mode) {
        case 'isibak':
            // Mode isibak
            saveData("silagung-controller/relay/relay1", true);
            saveData("silagung-controller/relay/relay2", true);
            saveData("silagung-controller/relay/relay3", false);
            saveData("silagung-controller/relay/relay4", true);
            saveData("silagung-controller/relay/relay5", false);
            saveData("silagung-controller/relay/relay6", false);
            break;
            
        case 'mixing':
            // Mode mixing
            saveData("silagung-controller/relay/relay1", false);
            saveData("silagung-controller/relay/relay2", false);
            saveData("silagung-controller/relay/relay3", true);
            saveData("silagung-controller/relay/relay4", true);
            saveData("silagung-controller/relay/relay5", false);
            saveData("silagung-controller/relay/relay6", false);
            break;
            
        case 'supply':
            // Mode supply
            saveData("silagung-controller/relay/relay1", true);
            saveData("silagung-controller/relay/relay2", false);
            saveData("silagung-controller/relay/relay3", false);
            saveData("silagung-controller/relay/relay4", false);
            saveData("silagung-controller/relay/relay5", true);
            saveData("silagung-controller/relay/relay6", false);
            break;

        case 'all_off':
            // Matikan semua relay
            for ($i = 1; $i <= 6; $i++) {
                saveData("silagung-controller/relay/relay{$i}", false);
            }
            break;
    }
}

// Ambil status terkini
$relay_status = [];
for ($i = 1; $i <= 6; $i++) {
    $relay_status[$i] = getData("silagung-controller/relay/relay{$i}");
}

$vfd_status = getData("silagung-controller/commands/vfd");
$vfd_frequency = getData("silagung-controller/commands/frequency") ?? 50;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silagung Controller</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4">Silagung Controller</h1>
        
        <!-- VFD Control Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Kontrol VFD</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="update_vfd" value="1">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="vfd_status" 
                                       id="vfdSwitch" <?php echo $vfd_status ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="vfdSwitch">VFD On/Off</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="freqSlider" class="form-label">
                                Frekuensi: <span id="freqValue"><?php echo $vfd_frequency; ?></span> Hz
                            </label>
                            <input type="range" class="form-range" id="freqSlider" name="frequency"
                                   min="0" max="50" value="<?php echo $vfd_frequency; ?>">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Update VFD</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mode Control Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Mode Kontrol Relay</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="set_mode" value="1">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <button type="submit" name="mode" value="isibak" class="btn btn-outline-primary w-100">Mode Isi Bak</button>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="mode" value="mixing" class="btn btn-outline-success w-100">Mode Mixing</button>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="mode" value="supply" class="btn btn-outline-info w-100">Mode Supply</button>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="mode" value="all_off" class="btn btn-outline-danger w-100">Matikan Semua Relay</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Relay Control Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Kontrol Relay</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div class="col-md-4">
                        <form method="POST" action="" class="d-flex align-items-center">
                            <input type="hidden" name="update_relay" value="1">
                            <input type="hidden" name="relay_number" value="<?php echo $i; ?>">
                            <div class="form-check form-switch flex-grow-1">
                                <input class="form-check-input" type="checkbox" 
                                       name="status" value="on"
                                       <?php echo $relay_status[$i] ? 'checked' : ''; ?>
                                       onchange="this.form.submit()">
                                <label class="form-check-label">Relay <?php echo $i; ?></label>
                            </div>
                        </form>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update nilai frekuensi saat slider digerakkan
        document.getElementById('freqSlider').addEventListener('input', function(e) {
            document.getElementById('freqValue').textContent = e.target.value;
        });
    </script>
</body>
</html> 