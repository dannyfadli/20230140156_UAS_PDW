<?php
session_start();
require_once '../config.php';

$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
require_once 'templates/header_mahasiswa.php';

$user_id = $_SESSION['user_id'];

// Proses pendaftaran
if (isset($_POST['daftar'])) {
    $praktikum_id = $_POST['praktikum_id'];

    // Cek apakah sudah terdaftar
    $cek = mysqli_query($conn, "SELECT * FROM praktikum_mahasiswa WHERE user_id=$user_id AND praktikum_id=$praktikum_id");

    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = 'Kamu sudah terdaftar di praktikum ini.';
    } else {
        mysqli_query($conn, "INSERT INTO praktikum_mahasiswa (user_id, praktikum_id) VALUES ($user_id, $praktikum_id)");
        $_SESSION['success'] = 'Berhasil mendaftar ke praktikum!';
    }

    header("Location: cari_praktikum.php");
    exit();
}

// Ambil praktikum yang belum diikuti user
$praktikum = mysqli_query($conn, "
    SELECT * FROM praktikum 
    WHERE id NOT IN (
        SELECT praktikum_id FROM praktikum_mahasiswa WHERE user_id = $user_id
    )
    ORDER BY created_at DESC
");
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Daftar Praktikum Tersedia</h1>

<?php if (mysqli_num_rows($praktikum) === 0): ?>
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg">
        Kamu sudah mendaftar semua praktikum yang tersedia.
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
<?php while ($p = mysqli_fetch_assoc($praktikum)): ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-blue-700"><?= htmlspecialchars($p['nama_praktikum']) ?></h2>
        <p class="text-sm text-gray-600"><?= htmlspecialchars($p['deskripsi']) ?></p>
        <p class="mt-2 text-sm text-gray-500 italic">Semester: <?= htmlspecialchars($p['semester']) ?></p>

        <form method="POST" class="mt-4">
            <input type="hidden" name="praktikum_id" value="<?= $p['id'] ?>">
            <button type="submit" name="daftar" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                Daftar Sekarang
            </button>
        </form>
    </div>
<?php endwhile; ?>
</div>

<!-- SweetAlert -->
<?php if (isset($_SESSION['success'])): ?>
<script>
Swal.fire({
    title: 'Berhasil!',
    text: '<?= $_SESSION['success'] ?>',
    icon: 'success',
    confirmButtonText: 'OK'
});
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    title: 'Gagal!',
    text: '<?= $_SESSION['error'] ?>',
    icon: 'error',
    confirmButtonText: 'OK'
});
</script>
<?php unset($_SESSION['error']); endif; ?>

<?php require_once 'templates/footer_mahasiswa.php'; ?>