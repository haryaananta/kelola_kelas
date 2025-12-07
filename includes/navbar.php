<?php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">SmartKelas</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="jadwal_pelajaran.php">Jadwal Pelajaran</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="jadwal_piket.php">Jadwal Piket</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tugas.php">Tugas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="kas.php">Kas Kelas</a>
                </li>
            </ul>
            <span class="navbar-text me-3">
                Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?> 
                (<?php echo htmlspecialchars($_SESSION['role']); ?>
                <?php if (isset($_SESSION['kelas']) && !empty($_SESSION['kelas'])):
                    echo ' - ' . htmlspecialchars($_SESSION['kelas']);
                endif; ?>)
            </span>
            <a href="logout.php" class="btn btn-light">Logout</a>
        </div>
    </div>
</nav>