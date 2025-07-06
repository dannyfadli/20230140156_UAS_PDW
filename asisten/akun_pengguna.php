<?php
session_start();
require_once '../config.php';

// Tambah Akun
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Cek email unik
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = 'Email sudah digunakan.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $nama, $email, $password, $role);
        mysqli_stmt_execute($stmt);
        $_SESSION['success'] = 'Akun berhasil ditambahkan';
    }

    header('Location: akun_pengguna.php');
    exit();
}

// Edit Akun
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, email=?, role=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'sssi', $nama, $email, $role, $id);
    mysqli_stmt_execute($stmt);
    $_SESSION['success'] = 'Akun berhasil diperbarui';
    header('Location: akun_pengguna.php');
    exit();
}

// Hapus Akun
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    $_SESSION['success'] = 'Akun berhasil dihapus';
    header('Location: akun_pengguna.php');
    exit();
}

// Ambil semua akun
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

$pageTitle = 'Manajemen Akun';
$activePage = 'akun';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-bold mb-4">Tambah Akun Pengguna</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block">Nama</label>
            <input type="text" name="nama" class="w-full p-2 border rounded" required>
        </div>
        <div>
            <label class="block">Email</label>
            <input type="email" name="email" class="w-full p-2 border rounded" required>
        </div>
        <div>
            <label class="block">Password</label>
            <input type="password" name="password" class="w-full p-2 border rounded" required>
        </div>
        <div>
            <label class="block">Role</label>
            <select name="role" class="w-full p-2 border rounded" required>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <button type="submit" name="tambah" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Daftar Pengguna</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2">Nama</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Role</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($users)) : ?>
                <tr class="border-t">
                    <td class="p-2"><?= htmlspecialchars($u['nama']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="p-2 capitalize"><?= $u['role'] ?></td>
                    <td class="p-2 space-x-2">
                        <button onclick='editAkun(<?= json_encode($u) ?>)' class="text-yellow-600 hover:underline">Edit</button>
                        <a href="?hapus=<?= $u['id'] ?>" onclick="return confirm('Yakin ingin menghapus akun ini?')" class="text-red-600 hover:underline">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
    <form method="POST" class="bg-white p-6 rounded-lg w-full max-w-lg space-y-4 relative">
        <h2 class="text-xl font-bold">Edit Akun Pengguna</h2>
        <input type="hidden" name="id" id="edit_id">
        <div>
            <label>Nama</label>
            <input type="text" name="nama" id="edit_nama" class="w-full p-2 border rounded" required>
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" id="edit_email" class="w-full p-2 border rounded" required>
        </div>
        <div>
            <label>Role</label>
            <select name="role" id="edit_role" class="w-full p-2 border rounded" required>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
        </div>
        <div class="flex justify-end space-x-2">
            <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 bg-gray-400 text-white rounded">Batal</button>
            <button type="submit" name="update" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Simpan</button>
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

<script>
function editAkun(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_nama').value = user.nama;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('modalEdit').classList.remove('hidden');
}
</script>

<?php require_once 'templates/footer.php'; ?>
