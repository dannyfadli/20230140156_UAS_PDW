<?php
$pageTitle = 'Manajemen Modul';
$activePage = 'modul';
require_once '../config.php';
require_once 'templates/header.php';

// Ambil semua praktikum untuk dropdown
$praktikum_result = mysqli_query($conn, "SELECT * FROM praktikum");

// Proses Tambah Modul
if (isset($_POST['tambah'])) {
    $praktikum_id = $_POST['praktikum_id'];
    $nama_modul = $_POST['nama_modul'];
    $deskripsi = $_POST['deskripsi'];

    $file_materi = '';
    if ($_FILES['file_materi']['name']) {
        $ext = pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION);
        $file_materi = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['file_materi']['tmp_name'], '../uploads/' . $file_materi);
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO modul_praktikum (praktikum_id, nama_modul, deskripsi, file_materi) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isss', $praktikum_id, $nama_modul, $deskripsi, $file_materi);
    mysqli_stmt_execute($stmt);
    echo "<script>alert('Modul berhasil ditambahkan');location.href='modul.php';</script>";
}

// Proses Hapus Modul
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM modul_praktikum WHERE id = $id");
    echo "<script>alert('Modul berhasil dihapus');location.href='modul.php';</script>";
}

// Proses Edit Modul
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $praktikum_id = $_POST['praktikum_id'];
    $nama_modul = $_POST['nama_modul'];
    $deskripsi = $_POST['deskripsi'];

    $file_materi = $_POST['file_materi_lama'];
    if ($_FILES['file_materi']['name']) {
        $ext = pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION);
        $file_materi = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['file_materi']['tmp_name'], '../uploads/' . $file_materi);
    }

    $stmt = mysqli_prepare($conn, "UPDATE modul_praktikum SET praktikum_id=?, nama_modul=?, deskripsi=?, file_materi=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'isssi', $praktikum_id, $nama_modul, $deskripsi, $file_materi, $id);
    mysqli_stmt_execute($stmt);
    echo "<script>alert('Modul berhasil diperbarui');location.href='modul.php';</script>";
}

// Ambil semua modul
$modul_result = mysqli_query($conn, "
    SELECT m.*, p.nama_praktikum 
    FROM modul_praktikum m 
    JOIN praktikum p ON m.praktikum_id = p.id
    ORDER BY m.created_at DESC
");
?>

<!-- Form Tambah Modul -->
<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-bold mb-4">Tambah Modul</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block font-medium">Mata Praktikum</label>
            <select name="praktikum_id" class="w-full p-2 border rounded" required>
                <option value="">-- Pilih Praktikum --</option>
                <?php while ($p = mysqli_fetch_assoc($praktikum_result)) : ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_praktikum']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label class="block font-medium">Nama Modul</label>
            <input type="text" name="nama_modul" class="w-full p-2 border rounded" required>
        </div>
        <div>
            <label class="block font-medium">Deskripsi</label>
            <textarea name="deskripsi" class="w-full p-2 border rounded"></textarea>
        </div>
        <div>
            <label class="block font-medium">Upload File Materi (PDF/DOCX)</label>
            <input type="file" name="file_materi" class="w-full p-2 border rounded">
        </div>
        <button type="submit" name="tambah" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
    </form>
</div>

<!-- Tabel Daftar Modul -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Daftar Modul</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full border">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2">Modul</th>
                    <th class="p-2">Praktikum</th>
                    <th class="p-2">Materi</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($m = mysqli_fetch_assoc($modul_result)) : ?>
                    <tr class="border-t">
                        <td class="p-2">
                            <strong><?= htmlspecialchars($m['nama_modul']) ?></strong><br>
                            <span class="text-sm text-gray-600"><?= htmlspecialchars($m['deskripsi']) ?></span>
                        </td>
                        <td class="p-2"><?= htmlspecialchars($m['nama_praktikum']) ?></td>
                        <td class="p-2">
                            <?php if ($m['file_materi']) : ?>
                                <a href="../uploads/<?= $m['file_materi'] ?>" class="text-blue-600 underline" target="_blank">Download</a>
                            <?php else : ?>
                                <span class="text-gray-500">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-2 space-x-2">
                            <!-- Tombol Edit: memuat form inline -->
                            <button onclick="editModul(<?= htmlspecialchars(json_encode($m)) ?>)" class="text-yellow-600 hover:underline">Edit</button>
                            <a href="?hapus=<?= $m['id'] ?>" onclick="return confirm('Yakin ingin menghapus modul ini?')" class="text-red-600 hover:underline">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Update (diisi lewat JavaScript) -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg w-full max-w-lg space-y-4 relative">
        <h2 class="text-xl font-bold">Edit Modul</h2>
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="file_materi_lama" id="edit_file_lama">

        <div>
            <label class="block">Mata Praktikum</label>
            <select name="praktikum_id" id="edit_praktikum" class="w-full p-2 border rounded" required>
                <?php
                $praktikum_result = mysqli_query($conn, "SELECT * FROM praktikum");
                while ($p = mysqli_fetch_assoc($praktikum_result)) :
                ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_praktikum']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label class="block">Nama Modul</label>
            <input type="text" name="nama_modul" id="edit_nama" class="w-full p-2 border rounded" required>
        </div>
        <div>
            <label class="block">Deskripsi</label>
            <textarea name="deskripsi" id="edit_deskripsi" class="w-full p-2 border rounded"></textarea>
        </div>
        <div>
            <label class="block">Upload File Baru (opsional)</label>
            <input type="file" name="file_materi" class="w-full p-2 border rounded">
        </div>
        <div class="flex justify-end space-x-2">
            <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 bg-gray-400 text-white rounded">Batal</button>
            <button type="submit" name="update" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Simpan</button>
        </div>
    </form>
</div>

<script>
function editModul(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_file_lama').value = data.file_materi;
    document.getElementById('edit_nama').value = data.nama_modul;
    document.getElementById('edit_deskripsi').value = data.deskripsi;
    document.getElementById('edit_praktikum').value = data.praktikum_id;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>

<?php require_once 'templates/footer.php'; ?>