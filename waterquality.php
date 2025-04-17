<?php
require_once 'dbcon.php';

// Fungsi untuk mendapatkan data kualitas air
function getWaterQualityData() {
    return getData('waterquality');
}

// Ambil data terbaru
$waterData = getWaterQualityData();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Kualitas Air</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            font-weight: bold;
        }
        .card-value {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .bg-ph {
            background-color: #3498db;
            color: white;
        }
        .bg-tds {
            background-color: #2ecc71;
            color: white;
        }
        .bg-temp {
            background-color: #e74c3c;
            color: white;
        }
        .last-update {
            font-size: 0.8rem;
            color: #7f8c8d;
        }
        .dashboard-title {
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center dashboard-title">
            Monitoring Kualitas Air
        </h1>
        
        <div class="row mt-4">
            <!-- Karti Nilai pH -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-ph">
                        <i class="fas fa-vial me-2"></i>Nilai pH
                    </div>
                    <div class="card-body text-center">
                        <div class="card-value"><?php echo isset($waterData['ph']) ? number_format($waterData['ph'], 1) : 'N/A'; ?></div>
                        <p class="mt-2">
                            <?php
                            if (isset($waterData['ph'])) {
                                $ph = $waterData['ph'];
                                if ($ph < 6.5) {
                                    echo '<span class="badge bg-danger">Terlalu Asam</span>';
                                } elseif ($ph > 8.5) {
                                    echo '<span class="badge bg-danger">Terlalu Basa</span>';
                                } else {
                                    echo '<span class="badge bg-success">Normal</span>';
                                }
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Karti Nilai TDS -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-tds">
                        <i class="fas fa-flask me-2"></i>TDS (PPM)
                    </div>
                    <div class="card-body text-center">
                        <div class="card-value"><?php echo isset($waterData['tds']) ? number_format($waterData['tds'], 0) : 'N/A'; ?></div>
                        <p class="mt-2">
                            <?php
                            if (isset($waterData['tds'])) {
                                $tds = $waterData['tds'];
                                if ($tds < 100) {
                                    echo '<span class="badge bg-info">Sangat Baik</span>';
                                } elseif ($tds < 500) {
                                    echo '<span class="badge bg-success">Baik</span>';
                                } elseif ($tds < 1000) {
                                    echo '<span class="badge bg-warning">Sedang</span>';
                                } else {
                                    echo '<span class="badge bg-danger">Buruk</span>';
                                }
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Karti Suhu -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-temp">
                        <i class="fas fa-temperature-high me-2"></i>Suhu (°C)
                    </div>
                    <div class="card-body text-center">
                        <div class="card-value"><?php echo isset($waterData['temperature']) ? number_format($waterData['temperature'], 1) : 'N/A'; ?></div>
                        <p class="mt-2">
                            <?php
                            if (isset($waterData['temperature'])) {
                                $temp = $waterData['temperature'];
                                if ($temp < 20) {
                                    echo '<span class="badge bg-info">Dingin</span>';
                                } elseif ($temp < 30) {
                                    echo '<span class="badge bg-success">Normal</span>';
                                } else {
                                    echo '<span class="badge bg-warning">Panas</span>';
                                }
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Informasi Parameter Kualitas Air</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Nilai Ideal</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>pH</td>
                                    <td>6.5 - 8.5</td>
                                    <td>pH mengukur tingkat keasaman air. Nilai di luar rentang ideal dapat menunjukkan pencemaran air.</td>
                                </tr>
                                <tr>
                                    <td>TDS</td>
                                    <td>&lt; 500 PPM</td>
                                    <td>Total Dissolved Solids (TDS) mengukur jumlah zat terlarut dalam air. Nilai yang tinggi dapat menunjukkan adanya kontaminan.</td>
                                </tr>
                                <tr>
                                    <td>Suhu</td>
                                    <td>20 - 30 °C</td>
                                    <td>Suhu air yang ideal untuk kondisi umum. Suhu yang terlalu tinggi dapat mengurangi kadar oksigen terlarut.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>

    <script>
        // Konfigurasi Firebase
        const firebaseConfig = {
            apiKey: "YOUR_API_KEY",
            authDomain: "silagung.firebaseapp.com",
            databaseURL: "https://silagung-default-rtdb.asia-southeast1.firebasedatabase.app",
            projectId: "silagung",
            storageBucket: "silagung.appspot.com",
            messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
            appId: "YOUR_APP_ID"
        };
        
        // Inisialisasi Firebase
        firebase.initializeApp(firebaseConfig);
        
        // Fungsi untuk memperbarui nilai pada halaman
        function updateValues(data) {
            // Update nilai pH
            if (data.ph !== undefined) {
                $('.card-value').eq(0).text(parseFloat(data.ph).toFixed(1));
                
                let phStatus = '';
                if (data.ph < 6.5) {
                    phStatus = '<span class="badge bg-danger">Terlalu Asam</span>';
                } else if (data.ph > 8.5) {
                    phStatus = '<span class="badge bg-danger">Terlalu Basa</span>';
                } else {
                    phStatus = '<span class="badge bg-success">Normal</span>';
                }
                $('.card-body').eq(0).find('p').html(phStatus);
            }
            
            // Update nilai TDS
            if (data.tds !== undefined) {
                $('.card-value').eq(1).text(Math.round(data.tds));
                
                let tdsStatus = '';
                if (data.tds < 100) {
                    tdsStatus = '<span class="badge bg-info">Sangat Baik</span>';
                } else if (data.tds < 500) {
                    tdsStatus = '<span class="badge bg-success">Baik</span>';
                } else if (data.tds < 1000) {
                    tdsStatus = '<span class="badge bg-warning">Sedang</span>';
                } else {
                    tdsStatus = '<span class="badge bg-danger">Buruk</span>';
                }
                $('.card-body').eq(1).find('p').html(tdsStatus);
            }
            
            // Update nilai Suhu
            if (data.temperature !== undefined) {
                $('.card-value').eq(2).text(parseFloat(data.temperature).toFixed(1));
                
                let tempStatus = '';
                if (data.temperature < 20) {
                    tempStatus = '<span class="badge bg-info">Dingin</span>';
                } else if (data.temperature < 30) {
                    tempStatus = '<span class="badge bg-success">Normal</span>';
                } else {
                    tempStatus = '<span class="badge bg-warning">Panas</span>';
                }
                $('.card-body').eq(2).find('p').html(tempStatus);
            }
            
            // Update timestamp
            if (data.last_update) {
                let date = new Date(data.last_update);
                let formattedDate = 
                    ("0" + date.getDate()).slice(-2) + "-" +
                    ("0" + (date.getMonth() + 1)).slice(-2) + "-" +
                    date.getFullYear() + " " +
                    ("0" + date.getHours()).slice(-2) + ":" +
                    ("0" + date.getMinutes()).slice(-2) + ":" +
                    ("0" + date.getSeconds()).slice(-2);
                $('#lastUpdate').text(formattedDate);
            }
        }
        
        $(document).ready(function() {
            // Referensi ke node 'waterquality' di Firebase Realtime Database
            const waterQualityRef = firebase.database().ref('waterquality');
            
            // Listener untuk perubahan data, akan dipanggil setiap kali data berubah di Firebase
            waterQualityRef.on('value', (snapshot) => {
                const data = snapshot.val();
                if (data) {
                    // Evaluasi status untuk setiap parameter
                    let status = {
                        ph_status: 'unknown',
                        tds_status: 'unknown',
                        temperature_status: 'unknown'
                    };
                    
                    // Evaluasi status pH
                    if (data.ph !== undefined) {
                        if (data.ph < 6.5) {
                            status.ph_status = 'terlalu_asam';
                        } else if (data.ph > 8.5) {
                            status.ph_status = 'terlalu_basa';
                        } else {
                            status.ph_status = 'normal';
                        }
                    }
                    
                    // Evaluasi status TDS
                    if (data.tds !== undefined) {
                        if (data.tds < 100) {
                            status.tds_status = 'sangat_baik';
                        } else if (data.tds < 500) {
                            status.tds_status = 'baik';
                        } else if (data.tds < 1000) {
                            status.tds_status = 'sedang';
                        } else {
                            status.tds_status = 'buruk';
                        }
                    }
                    
                    // Evaluasi status suhu
                    if (data.temperature !== undefined) {
                        if (data.temperature < 20) {
                            status.temperature_status = 'dingin';
                        } else if (data.temperature < 30) {
                            status.temperature_status = 'normal';
                        } else {
                            status.temperature_status = 'panas';
                        }
                    }
                    
                    // Gabungkan data dan status
                    const resultData = {...data, ...status};
                    
                    // Update UI dengan data terbaru
                    updateValues(resultData);
                    console.log('Data berhasil diperbarui secara real-time');
                    
                    // Animasi untuk menandakan pembaruan
                    $('.card').addClass('animate__animated animate__pulse');
                    setTimeout(function() {
                        $('.card').removeClass('animate__animated animate__pulse');
                    }, 1000);
                }
            });
        });
        
        // Fungsi untuk me-refresh halaman manual
    </script>
</body>
</html>



    