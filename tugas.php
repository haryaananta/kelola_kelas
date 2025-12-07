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
$tugas_list = [];
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
    $selected_kelas = $_SESSION['kelas'] ?? null;
}

// Handle form submission to add a new task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($role == 'Admin' || $role == 'Siswa')) {
    if (!empty($selected_kelas)) { // Can only add task if a class is active
        $pelajaran = $_POST['pelajaran'];
        $deskripsi = $_POST['deskripsi'];
        $deadline = $_POST['deadline'];
        $status = 'Baru'; // Default status

        $sql = "INSERT INTO tugas (pelajaran, deskripsi, deadline, status, kelas) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $pelajaran, $deskripsi, $deadline, $status, $selected_kelas);
        $stmt->execute();
        // Redirect to prevent form resubmission
        $redirect_url = 'tugas.php';
        if ($role == 'Guru') {
            $redirect_url .= '?kelas=' . urlencode($selected_kelas);
        }
        header("Location: " . $redirect_url);
        exit();
    }
}

// Fetch task data if a class is selected
if (!empty($selected_kelas)) {
    $sql = "SELECT * FROM tugas WHERE kelas = ? ORDER BY deadline ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_kelas);
    $stmt->execute();
    $tugas_list = $stmt->get_result();
}
?>

<div class="container mt-4">

    <?php if ($role == 'Guru'): ?>
        <h2 class="text-center mb-4">Lihat Daftar Tugas</h2>
        <form method="GET" class="row g-3 justify-content-center mb-4 align-items-center">
            <div class="col-auto">
                <label for="kelas-select" class="col-form-label">Pilih Kelas:</label>
            </div>
            <div class="col-auto">
                <select name="kelas" id="kelas-select" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Tampilkan Tugas --</option>
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
        <h2 class="text-center mb-4">Daftar Tugas - Kelas <?php echo htmlspecialchars($selected_kelas); ?></h2>

        <?php if ($role == 'Admin' || $role == 'Siswa'): ?>
        <div class="card mb-4">
            <div class="card-header">Tambah Tugas Baru</div>
            <div class="card-body">
                <form method="POST">
                    <div class="row gx-2">
                        <div class="col-md-3"><input type="text" class="form-control" name="pelajaran" placeholder="Mata Pelajaran" required></div>
                        <div class="col-md-4"><input type="text" class="form-control" name="deskripsi" placeholder="Deskripsi Tugas" required></div>
                        <div class="col-md-3"><input type="date" class="form-control" name="deadline" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Tambah</button></div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr><th>Mata Pelajaran</th><th>Deskripsi</th><th>Deadline</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if ($tugas_list && $tugas_list->num_rows > 0): ?>
                    <?php while ($row = $tugas_list->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['pelajaran']); ?></td>
                            <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                            <td><?php echo htmlspecialchars($row['deadline']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Belum ada tugas untuk kelas ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php elseif ($role == 'Guru'): ?>
        <div class="alert alert-info text-center">Silakan pilih kelas dari dropdown di atas untuk melihat daftar tugas.</div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>