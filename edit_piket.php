<?php
session_start();
include 'config/db.php';
include 'includes/header.php';
include 'includes/navbar.php';

$kelas = $_SESSION['kelas'];
$role = $_SESSION['role'];

if ($role != 'Admin') {
    header("Location: jadwal_piket.php");
    exit();
}

$hari = $_GET['hari'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_list = $_POST['siswa'];

    // Hapus jadwal piket lama untuk hari ini
    $sql_delete = "DELETE FROM jadwal_piket WHERE hari = ? AND kelas = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ss", $hari, $kelas);
    $stmt_delete->execute();

    // Tambah jadwal piket baru
    $sql_insert = "INSERT INTO jadwal_piket (hari, nama_siswa, kelas) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    foreach ($siswa_list as $siswa) {
        if (!empty($siswa)) {
            $stmt_insert->bind_param("sss", $hari, $siswa, $kelas);
            $stmt_insert->execute();
        }
    }

    header("Location: jadwal_piket.php");
    exit();
}

$sql = "SELECT nama_siswa FROM jadwal_piket WHERE hari = ? AND kelas = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hari, $kelas);
$stmt->execute();
$result = $stmt->get_result();

$piket = [];
while ($row = $result->fetch_assoc()) {
    $piket[] = $row['nama_siswa'];
}
?>

<div class="container mt-4">
    <h2 class="text-center mb-4">Edit Jadwal Piket - <?php echo $hari; ?></h2>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div id="siswa-container">
                    <?php if (count($piket) > 0): ?>
                        <?php foreach ($piket as $siswa): ?>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="siswa[]" value="<?php echo $siswa; ?>">
                                <button class="btn btn-danger remove-siswa" type="button">Hapus</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="siswa[]">
                            <button class="btn btn-danger remove-siswa" type="button">Hapus</button>
                        </div>
                    <?php endif; ?>
                </div>
                <button class="btn btn-success" type="button" id="add-siswa">Tambah Siswa</button>
                <hr>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="jadwal_piket.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-siswa').addEventListener('click', function() {
        var container = document.getElementById('siswa-container');
        var newInput = document.createElement('div');
        newInput.className = 'input-group mb-2';
        newInput.innerHTML = '<input type="text" class="form-control" name="siswa[]"><button class="btn btn-danger remove-siswa" type="button">Hapus</button>';
        container.appendChild(newInput);
    });

    document.getElementById('siswa-container').addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-siswa')) {
            e.target.closest('.input-group').remove();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>