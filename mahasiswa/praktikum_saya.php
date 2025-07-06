<?php
session_start();
require_once '../config.php';

$user_id = $_SESSION['user_id'];

// Proses upload laporan (WAJIB sebelum HTML)
if (isset($_POST['upload'])) {
    $modul_id = $_POST['modul_id'];
    $filename = $_FILES['file_laporan']['name'];
    $tmp = $_FILES['file_laporan']['tmp_name'];
    $filesize = $_FILES['file_laporan']['size'];

    $allowed_ext = ['pdf', 'doc', 'docx'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $max_size = 5 * 1024 * 1024;

    if (!is_dir('../uploads')) {
        mkdir('../uploads', 0777, true);
    }

    $new_name = uniqid() . '_' . basename($filename);
    $destination = "../uploads/" . $new_name;

    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['error'] = 'Format file tidak diizinkan.';
    } elseif ($filesize > $max_size) {
        $_SESSION['error'] = 'Ukuran file terlalu besar (maks. 5MB).';
    } elseif (move_uploaded_file($tmp, $destination)) {
        $cek = mysqli_query($conn, "SELECT * FROM laporan_mahasiswa WHERE user_id=$user_id AND modul_id=$modul_id");

        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($conn, "UPDATE laporan_mahasiswa SET file_laporan='$new_name', tanggal_upload=NOW(), nilai=NULL, feedback=NULL WHERE user_id=$user_id AND modul_id=$modul_id");
        } else {
            mysqli_query($conn, "INSERT INTO laporan_mahasiswa (user_id, modul_id, file_laporan) VALUES ($user_id, $modul_id, '$new_name')");
        }

        $_SESSION['success'] = 'Laporan berhasil diupload.';
    } else {
        $_SESSION['error'] = 'Gagal mengunggah file.';
    }

    header("Location: praktikum_saya.php");
    exit();
}
?>

<?php
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';

// Ambil daftar praktikum yang diikuti
$praktikum_result = mysqli_query($conn, "
    SELECT p.id, p.nama_praktikum, p.deskripsi, p.semester
    FROM praktikum_mahasiswa pm
    JOIN praktikum p ON pm.praktikum_id = p.id
    WHERE pm.user_id = $user_id
    ORDER BY p.nama_praktikum
");
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold mb-4 text-gray-800">Praktikum yang Kamu Ikuti</h1>

    <?php if (mysqli_num_rows($praktikum_result) === 0): ?>
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg">
            Kamu belum mendaftar ke praktikum manapun.
        </div>
    <?php endif; ?>

    <?php while ($praktikum = mysqli_fetch_assoc($praktikum_result)): ?>
        <div class="mb-6 border rounded-lg p-6 bg-white shadow">
            <h2 class="text-xl font-semibold text-blue-700"><?= htmlspecialchars($praktikum['nama_praktikum']) ?> (<?= $praktikum['semester'] ?>)</h2>
            <p class="text-gray-600"><?= htmlspecialchars($praktikum['deskripsi']) ?></p>

            <!-- Modul Praktikum -->
            <div class="mt-4">
                <h3 class="text-lg font-bold text-gray-700 mb-2">Modul Praktikum:</h3>
                <?php
                $praktikum_id = $praktikum['id'];
                $modul_result = mysqli_query($conn, "
                    SELECT m.*, l.file_laporan, l.nilai, l.feedback
                    FROM modul_praktikum m
                    LEFT JOIN laporan_mahasiswa l 
                        ON l.modul_id = m.id AND l.user_id = $user_id
                    WHERE m.praktikum_id = $praktikum_id
                    ORDER BY m.id ASC
                ");
                ?>

                <?php if (mysqli_num_rows($modul_result) > 0): ?>
                    <table class="w-full border mt-2 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left">Modul</th>
                                <th class="p-2">File</th>
                                <th class="p-2">Nilai</th>
                                <th class="p-2">Feedback</th>
                                <th class="p-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($modul = mysqli_fetch_assoc($modul_result)): ?>
                                <tr class="border-t">
                                    <td class="p-2"><?= htmlspecialchars($modul['nama_modul']) ?></td>
                                    <td class="p-2 text-center">
                                        <?php if ($modul['file_laporan']): ?>
                                            <a href="../uploads/<?= $modul['file_laporan'] ?>" class="text-blue-500 underline" target="_blank">Lihat</a>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Belum Upload</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-2 text-center"><?= $modul['nilai'] !== null ? $modul['nilai'] : '-' ?></td>
                                    <td class="p-2"><?= $modul['feedback'] ?: '-' ?></td>
                                    <td class="p-2 text-center">
                                        <form method="POST" enctype="multipart/form-data" class="inline-block" style="width: 100%;">
                                            <input type="hidden" name="modul_id" value="<?= $modul['id'] ?>">
                                            <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" required class="text-sm mb-1 w-full">
                                            <button type="submit" name="upload" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm mt-1 w-full">Upload</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-gray-500 italic mt-2">Belum ada modul.</p>
                <?php endif; ?>
            </div>
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