<?php
session_start();
include 'config/db.php';
include 'includes/header.php';
include 'includes/navbar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$jadwal = [];
$kelas_list = [];
$selected_kelas = null;

// Fetch class list for Guru
if ($role == 'Guru') {
    $sql_kelas = "SELECT DISTINCT kelas FROM users ORDER BY kelas ASC";
    $result_kelas = $conn->query($sql_kelas);
    if ($result_kelas && $result_kelas->num_rows > 0) {
        while($row = $result_kelas->fetch_assoc()) {
            $kelas_list[] = $row['kelas'];
        }
    }
    $selected_kelas = $_GET['kelas'] ?? null;
} else {
    // For Admin and Siswa, the class is from the session
    $selected_kelas = $_SESSION['kelas'];
}

// Fetch schedule data if a class is selected
if (!empty($selected_kelas)) {
    $sql = "SELECT * FROM jadwal_pelajaran WHERE kelas = ? ORDER BY hari, jam_ke";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_kelas);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $jadwal[$row['hari']][] = $row;
    }
}

$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
$jam_pelajaran = [
    1 => '07:00 - 07:40', 2 => '07:40 - 08:20', 3 => '08:20 - 09:00',
    'Istirahat 1' => '09:00 - 10:00',
    4 => '10:00 - 10:40', 5 => '10:40 - 11:20', 6 => '11:20 - 11:45',
    'Istirahat 2' => '11:45 - 12:15',
    7 => '12:15 - 12:55', 8 => '12:55 - 13:35', 9 => '13:35 - 14:15',
    10 => '14:15 - 14:55', 11 => '14:55 - 15:35'
];
?>

<div class="container mt-4">
    
    <?php if ($role == 'Guru'): ?>
        <h2 class="text-center mb-4">Lihat Jadwal Pelajaran</h2>
        <form method="GET" class="row g-3 justify-content-center mb-4 align-items-center">
            <div class="col-auto">
                <label for="kelas-select" class="col-form-label">Pilih Kelas:</label>
            </div>
            <div class="col-auto">
                <select name="kelas" id="kelas-select" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Tampilkan Jadwal --</option>
                    <?php foreach ($kelas_list as $kelas_item): ?>
                        <option value="<?php echo htmlspecialchars($kelas_item); ?>" <?php echo ($selected_kelas == $kelas_item) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kelas_item); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!empty($selected_kelas)): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Jadwal Pelajaran - Kelas <?php echo htmlspecialchars($selected_kelas); ?></h2>
            <?php if ($role == 'Admin'): ?>
                <a href="edit_jadwal_pelajaran.php" class="btn btn-warning">Edit Jadwal</a>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Jam Ke-</th>
                        <th>Waktu</th>
                        <?php foreach ($hari_list as $hari): ?>
                            <th><?php echo $hari; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($jam_pelajaran as $jam => $waktu) {
                        echo "<tr>";
                        echo "<td>{$jam}</td>";
                        echo "<td>{$waktu}</td>";

                        if (strpos($jam, 'Istirahat') !== false) {
                            echo "<td colspan='5' class='text-center table-success'>ISTIRAHAT</td>";
                        } else {
                            foreach ($hari_list as $hari) {
                                $pelajaran_ditemukan = false;
                                if (isset($jadwal[$hari])) {
                                    foreach ($jadwal[$hari] as $j) {
                                        if ($j['jam_ke'] == $jam) {
                                            echo "<td>{$j['pelajaran']} <br><small class='text-muted'>({$j['guru']})</small></td>";
                                            $pelajaran_ditemukan = true;
                                            break;
                                        }
                                    }
                                }
                                if (!$pelajaran_ditemukan) {
                                    echo "<td>-</td>";
                                }
                            }
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($role == 'Guru'): ?>
        <div class="alert alert-info text-center">Silakan pilih kelas dari dropdown di atas untuk melihat jadwal pelajaran.</div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>