<?php
$pageTitle = 'Manajemen Praktikum';
$activePage = 'praktikum';
require_once '../config.php';
require_once 'templates/header.php';

// Tambah Praktikum
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_praktikum'];
    $deskripsi = $_POST['deskripsi'];
    $semester = $_POST['semester'];

    $stmt = mysqli_prepare($conn, "INSERT INTO praktikum (nama_praktikum, deskripsi, semester) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sss', $nama, $deskripsi, $semester);
    mysqli_stmt_execute($stmt);
    echo "<script>alert('Praktikum berhasil ditambahkan');location.href='praktikum.php';</script>";
}

// Update Praktikum
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_praktikum'];
    $deskripsi = $_POST['deskripsi'];
    $semester = $_POST['semester'];

    $stmt = mysqli_prepare($conn, "UPDATE praktikum SET nama_praktikum=?, deskripsi=?, semester=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'sssi', $nama, $deskripsi, $semester, $id);
    mysqli_stmt_execute($stmt);
    echo "<script>alert('Praktikum berhasil diperbarui');location.href='praktikum.php';</script>";
}

// Hapus Praktikum
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM praktikum WHERE id = $id");
    echo "<script>alert('Praktikum berhasil dihapus');location.href='praktikum.php';</script>";
}

// Ambil Semua Praktikum
$praktikum_result = mysqli_query($conn, "SELECT * FROM praktikum ORDER BY created_at DESC");
?>

<!-- Form Tambah Praktikum -->
<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-bold mb-4">Tambah Praktikum</h2>
    <form method="POST" class="space-y-4">
        <div>
            <label class="block font-medium">Nama Praktikum</label>
            <input type="text" name="nama_praktikum" class="w-full p-2 border rounded" required>
        </div>
        <div>
            <label class="block font-medium">Deskripsi</label>
            <textarea name="deskripsi" class="w-full p-2 border rounded"></textarea>
        </div>
        <div>
            <label class="block font-medium">Semester</label>
            <input type="text" name="semester" class="w-full p-2 border rounded" required>
        </div>
        <button type="submit" name="tambah" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
    </form>
</div>

<!-- Tabel Praktikum -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Daftar Praktikum</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full border">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2">Nama Praktikum</th>
                    <th class="p-2">Semester</th>
                    <th class="p-2">Deskripsi</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = mysqli_fetch_assoc($praktikum_result)) : ?>
                    <tr class="border-t">
                        <td class="p-2 font-medium"><?= htmlspecialchars($p['nama_praktikum']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($p['semester']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($p['deskripsi']) ?></td>
                        <td class="p-2 space-x-2">
                            <button onclick="editPraktikum(<?= htmlspecialchars(json_encode($p)) ?>)" class="text-yellow-600 hover:underline">Edit</button>
                            <a href="?hapus=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')" class="text-red-600 hover:underline">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
    <form method="POST" class="bg-white p-6 rounded-lg w-full max-w-lg space-y-4 relative">
        <h2 class="text-xl font-bold">Edit Praktikum</h2>
        <input type="hidden" name="id" id="edit_id">
        <div>
            <label class="block">Nama Praktikum</label>
            <input type="text" name="nama_praktikum" id="edit_nama" class="w-full p-2 border rounded" required>
        </div>
        <div>
            <label class="block">Deskripsi</label>
            <textarea name="deskripsi" id="edit_deskripsi" class="w-full p-2 border rounded"></textarea>
        </div>
        <div>
            <label class="block">Semester</label>
            <input type="text" name="semester" id="edit_semester" class="w-full p-2 border rounded" required>
        </div>
        <div class="flex justify-end space-x-2">
            <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 bg-gray-400 text-white rounded">Batal</button>
            <button type="submit" name="update" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Simpan</button>
        </div>
    </form>
</div>

<script>
function editPraktikum(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama').value = data.nama_praktikum;
    document.getElementById('edit_deskripsi').value = data.deskripsi;
    document.getElementById('edit_semester').value = data.semester;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>

<?php require_once 'templates/footer.php'; ?>
