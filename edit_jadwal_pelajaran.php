<?php
session_start();
include 'config/db.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Role check
if ($_SESSION['role'] != 'Admin') {
    header("Location: jadwal_pelajaran.php");
    exit();
}

$kelas = $_SESSION['kelas'];
$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
$jam_pelajaran = [
    1 => '07:00 - 07:40', 2 => '07:40 - 08:20', 3 => '08:20 - 09:00',
    'Istirahat 1' => '09:00 - 10:00',
    4 => '10:00 - 10:40', 5 => '10:40 - 11:20', 6 => '11:20 - 11:45',
    'Istirahat 2' => '11:45 - 12:15',
    7 => '12:15 - 12:55', 8 => '12:55 - 13:35', 9 => '13:35 - 14:15',
    10 => '14:15 - 14:55', 11 => '14:55 - 15:35'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Clear old schedule for the class
    $sql_delete = "DELETE FROM jadwal_pelajaran WHERE kelas = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("s", $kelas);
    $stmt_delete->execute();

    // Insert new schedule
    $sql_insert = "INSERT INTO jadwal_pelajaran (hari, jam_ke, pelajaran, guru, kelas) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);

    foreach ($hari_list as $hari) {
        foreach ($jam_pelajaran as $jam_ke => $waktu) {
            if (is_numeric($jam_ke)) {
                $pelajaran = $_POST['pelajaran'][$hari][$jam_ke] ?? '';
                $guru = $_POST['guru'][$hari][$jam_ke] ?? '';

                if (!empty($pelajaran) || !empty($guru)) {
                    $stmt_insert->bind_param("sisss", $hari, $jam_ke, $pelajaran, $guru, $kelas);
                    $stmt_insert->execute();
                }
            }
        }
    }
    header("Location: jadwal_pelajaran.php?status=updated");
    exit();
}

// Fetch existing schedule to pre-fill the form
$sql_fetch = "SELECT * FROM jadwal_pelajaran WHERE kelas = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("s", $kelas);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
$jadwal = [];
while ($row = $result->fetch_assoc()) {
    $jadwal[$row['hari']][$row['jam_ke']] = $row;
}

?>

<div class="container mt-4">
    <h2 class="text-center mb-4">Edit Jadwal Pelajaran - Kelas <?php echo $kelas; ?></h2>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <?php foreach ($hari_list as $hari): ?>
                                    <th class="text-center"><?php echo $hari; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jam_pelajaran as $jam_ke => $waktu): ?>
                                <tr>
                                    <td><strong><?php echo $waktu; ?></strong><br><small>Jam ke-<?php echo $jam_ke; ?></small></td>
                                    <?php if (is_numeric($jam_ke)): ?>
                                        <?php foreach ($hari_list as $hari): ?>
                                            <td>
                                                <div class="mb-2">
                                                    <input type="text" class="form-control form-control-sm" name="pelajaran[<?php echo $hari; ?>][<?php echo $jam_ke; ?>]" placeholder="Pelajaran" value="<?php echo htmlspecialchars($jadwal[$hari][$jam_ke]['pelajaran'] ?? ''); ?>">
                                                </div>
                                                <div>
                                                    <input type="text" class="form-control form-control-sm" name="guru[<?php echo $hari; ?>][<?php echo $jam_ke; ?>]" placeholder="Guru" value="<?php echo htmlspecialchars($jadwal[$hari][$jam_ke]['guru'] ?? ''); ?>">
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <td colspan="5" class="text-center table-success"><strong><?php echo $jam_ke; ?></strong></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
