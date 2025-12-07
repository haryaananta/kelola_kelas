<?php
session_start();
include 'includes/header.php';
include 'includes/navbar.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12 text-center">
            <h1>Selamat Datang di SmartKelas</h1>
            <p>Sistem Manajemen Kelas Terpadu</p>
        </div>
    </div>

    <?php if ($_SESSION['role'] == 'Guru'): ?>
        <div class="text-center mt-5">
            <p class="fs-5">Anda login sebagai Guru. Silakan pilih halaman (Jadwal, Tugas, dll.) dari menu navigasi di atas.</p>
            <p>Anda akan dapat memilih kelas yang ingin dilihat di setiap halaman.</p>
        </div>
    <?php else: ?>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Jadwal Pelajaran</h5>
                        <p class="card-text">Lihat jadwal pelajaran mingguan.</p>
                        <a href="jadwal_pelajaran.php" class="btn btn-primary">Lihat Jadwal</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Jadwal Piket</h5>
                        <p class="card-text">Lihat jadwal piket harian.</p>
                        <a href="jadwal_piket.php" class="btn btn-primary">Lihat Jadwal</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Pengingat Tugas</h5>
                        <p class="card-text">Lihat daftar tugas dan deadline.</p>
                        <a href="tugas.php" class="btn btn-primary">Lihat Tugas</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Catatan Kas</h5>
                        <p class="card-text">Lihat catatan keuangan kelas.</p>
                        <a href="kas.php" class="btn btn-primary">Lihat Kas</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>