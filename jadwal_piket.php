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
$piket = [];
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

// Fetch picket data if a class is selected
if (!empty($selected_kelas)) {
    $sql = "SELECT * FROM jadwal_piket WHERE kelas = ? ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_kelas);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $piket[$row['hari']][] = $row['nama_siswa'];
    }
}

$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
?>

<div class="container mt-4">

    <?php if ($role == 'Guru'): ?>
        <h2 class="text-center mb-4">Lihat Jadwal Piket</h2>
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
        <h2 class="text-center mb-4">Jadwal Piket - Kelas <?php echo htmlspecialchars($selected_kelas); ?></h2>

        <div class="row">
            <?php foreach ($hari_list as $hari): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><?php echo $hari; ?></h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php if (isset($piket[$hari])): ?>
                                <?php foreach ($piket[$hari] as $siswa): ?>
                                    <li class="list-group-item"><?php echo htmlspecialchars($siswa); ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item">Belum ada jadwal piket.</li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($role == 'Admin'): ?>
                            <div class="card-body">
                                <a href="edit_piket.php?hari=<?php echo $hari; ?>" class="btn btn-sm btn-warning">Edit</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($role == 'Guru'): ?>
        <div class="alert alert-info text-center">Silakan pilih kelas dari dropdown di atas untuk melihat jadwal piket.</div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>