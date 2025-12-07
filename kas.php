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
$kas_list = [];
$kelas_list = [];
$selected_kelas = null;
$saldo = 0;

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
    $selected_kelas = $_SESSION['kelas'] ?? null;
}

// Handle form submission to add a new cash record
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $role == 'Admin') {
    if (!empty($selected_kelas)) {
        $keterangan = $_POST['keterangan'];
        $jenis = $_POST['jenis'];
        $jumlah = $_POST['jumlah'];
        $tanggal = date('Y-m-d');

        $sql = "INSERT INTO kas (tanggal, keterangan, jenis, jumlah, kelas) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $tanggal, $keterangan, $jenis, $jumlah, $selected_kelas);
        $stmt->execute();
        
        header("Location: kas.php");
        exit();
    }
}

// Fetch cash data if a class is selected
if (!empty($selected_kelas)) {
    // Fetch records
    $sql = "SELECT * FROM kas WHERE kelas = ? ORDER BY tanggal DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_kelas);
    $stmt->execute();
    $kas_list = $stmt->get_result();

    // Calculate balance
    $sql_saldo = "SELECT (SELECT COALESCE(SUM(jumlah), 0) FROM kas WHERE jenis = 'masuk' AND kelas = ?) - (SELECT COALESCE(SUM(jumlah), 0) FROM kas WHERE jenis = 'keluar' AND kelas = ?) as saldo";
    $stmt_saldo = $conn->prepare($sql_saldo);
    $stmt_saldo->bind_param("ss", $selected_kelas, $selected_kelas);
    $stmt_saldo->execute();
    $result_saldo = $stmt_saldo->get_result();
    $saldo = $result_saldo->fetch_assoc()['saldo'];
}
?>

<div class="container mt-4">

    <?php if ($role == 'Guru'): ?>
        <h2 class="text-center mb-4">Lihat Catatan Kas</h2>
        <form method="GET" class="row g-3 justify-content-center mb-4 align-items-center">
            <div class="col-auto"><label for="kelas-select" class="col-form-label">Pilih Kelas:</label></div>
            <div class="col-auto">
                <select name="kelas" id="kelas-select" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Tampilkan Kas --</option>
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
        <h2 class="text-center mb-4">Catatan Kas - Kelas <?php echo htmlspecialchars($selected_kelas); ?></h2>

        <div class="card mb-4">
            <div class="card-body text-center">
                <h5 class="card-title">Saldo Kas Saat Ini</h5>
                <p class="card-text fs-3">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></p>
            </div>
        </div>

        <?php if ($role == 'Admin'): ?>
        <div class="card mb-4">
            <div class="card-header">Tambah Catatan Kas</div>
            <div class="card-body">
                <form method="POST">
                    <div class="row gx-2">
                        <div class="col-md-5"><input type="text" class="form-control" name="keterangan" placeholder="Keterangan" required></div>
                        <div class="col-md-3">
                            <select class="form-select" name="jenis">
                                <option value="masuk">Masuk</option>
                                <option value="keluar">Keluar</option>
                            </select>
                        </div>
                        <div class="col-md-2"><input type="number" class="form-control" name="jumlah" placeholder="Jumlah" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Tambah</button></div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr><th>Tanggal</th><th>Keterangan</th><th>Jenis</th><th>Jumlah</th></tr>
            </thead>
            <tbody>
                <?php if ($kas_list && $kas_list->num_rows > 0): ?>
                    <?php while ($row = $kas_list->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($row['jenis'])); ?></td>
                            <td class="text-end">Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Belum ada catatan kas untuk kelas ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php elseif ($role == 'Guru'): ?>
        <div class="alert alert-info text-center">Silakan pilih kelas dari dropdown di atas untuk melihat catatan kas.</div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>