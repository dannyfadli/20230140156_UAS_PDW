<?php
session_start();
require_once '../config.php';

// Tambah atau Update Nilai & Feedback
if (isset($_POST['beri_nilai'])) {
    $id = $_POST['id'];
    $nilai = $_POST['nilai'];
    $feedback = $_POST['feedback'];

    $stmt = mysqli_prepare($conn, "UPDATE laporan_mahasiswa SET nilai = ?, feedback = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'isi', $nilai, $feedback, $id);
    mysqli_stmt_execute($stmt);
    $_SESSION['success'] = 'Nilai dan feedback berhasil disimpan';
    header('Location: laporan_masuk.php');
    exit();
}

// Hapus Laporan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM laporan_mahasiswa WHERE id = $id");
    $_SESSION['success'] = 'Laporan berhasil dihapus';
    header('Location: laporan_masuk.php');
    exit();
}

// Ambil semua laporan mahasiswa
$q = mysqli_query($conn, "
    SELECT l.*, u.nama AS nama_mahasiswa, m.nama_modul, p.nama_praktikum
    FROM laporan_mahasiswa l
    JOIN users u ON l.user_id = u.id
    JOIN modul_praktikum m ON l.modul_id = m.id
    JOIN praktikum p ON m.praktikum_id = p.id
    ORDER BY l.tanggal_upload DESC
");

$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Laporan Masuk Mahasiswa</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2">Mahasiswa</th>
                    <th class="p-2">Praktikum</th>
                    <th class="p-2">Modul</th>
                    <th class="p-2">File</th>
                    <th class="p-2">Nilai</th>
                    <th class="p-2">Feedback</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = mysqli_fetch_assoc($q)) : ?>
                    <tr class="border-t">
                        <td class="p-2 font-medium"><?= htmlspecialchars($r['nama_mahasiswa']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($r['nama_praktikum']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($r['nama_modul']) ?></td>
                        <td class="p-2">
                            <?php if ($r['file_laporan']) : ?>
                                <a href="../uploads/<?= $r['file_laporan'] ?>" class="text-blue-600 underline" target="_blank">Download</a>
                            <?php else : ?>
                                <span class="text-gray-400 italic">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-2"><?= $r['nilai'] !== null ? $r['nilai'] : '-' ?></td>
                        <td class="p-2"><?= $r['feedback'] ?: '-' ?></td>
                        <td class="p-2 space-x-2">
                            <button onclick='isiNilai(<?= json_encode($r) ?>)' class="text-green-600 hover:underline">Nilai</button>
                            <a href="?hapus=<?= $r['id'] ?>" onclick="return confirm('Hapus laporan ini?')" class="text-red-600 hover:underline">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Penilaian -->
<div id="formNilai" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
    <form method="POST" class="bg-white p-6 rounded-lg w-full max-w-lg space-y-4 relative">
        <h2 class="text-xl font-bold">Beri Nilai & Feedback</h2>
        <input type="hidden" name="id" id="nilai_id">
        <div>
            <label class="block">Nilai</label>
            <input type="number" name="nilai" id="nilai_nilai" class="w-full p-2 border rounded" min="0" max="100" required>
        </div>
        <div>
            <label class="block">Feedback</label>
            <textarea name="feedback" id="nilai_feedback" class="w-full p-2 border rounded"></textarea>
        </div>
        <div class="flex justify-end space-x-2">
            <button type="button" onclick="document.getElementById('formNilai').classList.add('hidden')" class="px-4 py-2 bg-gray-400 text-white rounded">Batal</button>
            <button type="submit" name="beri_nilai" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Simpan</button>
        </div>
    </form>
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

<script>
function isiNilai(data) {
    document.getElementById('nilai_id').value = data.id;
    document.getElementById('nilai_nilai').value = data.nilai ?? '';
    document.getElementById('nilai_feedback').value = data.feedback ?? '';
    document.getElementById('formNilai').classList.remove('hidden');
}
</script>

<?php require_once 'templates/footer.php'; ?>