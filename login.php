<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Fetch all unique classes from the database
$kelas_list = [];
$sql_kelas = "SELECT DISTINCT kelas FROM users ORDER BY kelas ASC";
$result_kelas = $conn->query($sql_kelas);
if ($result_kelas && $result_kelas->num_rows > 0) {
    while($row = $result_kelas->fetch_assoc()) {
        $kelas_list[] = $row['kelas'];
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Handle Admin/Guru Login
    if (isset($_POST['admin_login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        $kelas = $_POST['kelas'] ?? null;

        if (empty($username) || empty($password) || empty($role)) {
            $error = "Username, Password, dan Role harus diisi.";
        } else if ($role == 'Admin' && empty($kelas)) {
            $error = "Admin harus memilih kelas.";
        } else {
            if ($role == 'Admin') {
                $sql = "SELECT * FROM users WHERE username = ? AND role = ? AND kelas = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $username, $role, $kelas);
            } else { // Guru
                $sql = "SELECT * FROM users WHERE username = ? AND role = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $username, $role);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    if ($role == 'Admin') {
                        $_SESSION['kelas'] = $user['kelas'];
                    } else {
                        unset($_SESSION['kelas']);
                    }
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Password salah.";
                }
            } else {
                $error = "Kombinasi login tidak valid.";
            }
        }
    }

    // Handle Student Guest Login
    if (isset($_POST['student_login'])) {
        $kelas = $_POST['kelas'];
        if (!empty($kelas)) {
            $_SESSION['username'] = 'Siswa Tamu';
            $_SESSION['role'] = 'Siswa';
            $_SESSION['kelas'] = $kelas;
            header("Location: index.php");
            exit();
        } else {
            $error = "Silakan pilih kelas terlebih dahulu.";
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="login-container">
                <h2 class="text-center mb-4">Login SmartKelas</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <ul class="nav nav-tabs nav-fill mb-3" id="loginTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="admin-guru-tab" data-bs-toggle="tab" data-bs-target="#admin-guru-pane" type="button" role="tab" aria-controls="admin-guru-pane" aria-selected="true">Admin / Guru</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="siswa-tab" data-bs-toggle="tab" data-bs-target="#siswa-pane" type="button" role="tab" aria-controls="siswa-pane" aria-selected="false">Siswa</button>
                    </li>
                </ul>

                <div class="tab-content" id="loginTabContent">
                    <!-- Admin/Guru Login Form -->
                    <div class="tab-pane fade show active" id="admin-guru-pane" role="tabpanel" aria-labelledby="admin-guru-tab">
                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="Admin">Ketua Kelas (Admin)</option>
                                    <option value="Guru">Guru</option>
                                </select>
                            </div>
                            <div class="mb-3" id="kelas-admin-div">
                                <label for="kelas-admin" class="form-label">Kelas</label>
                                <select class="form-select" id="kelas-admin" name="kelas">
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach ($kelas_list as $kelas_item): ?>
                                        <option value="<?php echo htmlspecialchars($kelas_item); ?>"><?php echo htmlspecialchars($kelas_item); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" name="admin_login">Login</button>
                        </form>
                    </div>

                    <!-- Siswa Login Form -->
                    <div class="tab-pane fade" id="siswa-pane" role="tabpanel" aria-labelledby="siswa-tab">
                        <p class="text-center">Pilih kelas untuk melihat jadwal dan informasi lainnya.</p>
                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="kelas-siswa" class="form-label">Kelas</label>
                                <select class="form-select" id="kelas-siswa" name="kelas">
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach ($kelas_list as $kelas_item): ?>
                                        <option value="<?php echo htmlspecialchars($kelas_item); ?>"><?php echo htmlspecialchars($kelas_item); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100" name="student_login">Masuk sebagai Siswa</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const kelasAdminDiv = document.getElementById('kelas-admin-div');

    function toggleKelasInput() {
        if (!roleSelect || !kelasAdminDiv) return; // Guard clause
        if (roleSelect.value === 'Guru') {
            kelasAdminDiv.style.display = 'none';
        } else {
            kelasAdminDiv.style.display = 'block';
        }
    }

    // Initial check
    toggleKelasInput();

    // Listen for changes
    if(roleSelect) {
        roleSelect.addEventListener('change', toggleKelasInput);
    }
});
</script>

<?php include 'includes/footer.php'; ?>