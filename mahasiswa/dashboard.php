<?php
session_start();
require_once '../config.php';

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php';

// Ambil ID user dari session
$user_id = $_SESSION['user_id'];

// Hitung praktikum yang diikuti
$q1 = mysqli_query($conn, "SELECT COUNT(*) AS total_praktikum FROM praktikum_mahasiswa WHERE user_id = $user_id");
$praktikum = mysqli_fetch_assoc($q1)['total_praktikum'];

// Hitung tugas yang sudah dinilai
$q2 = mysqli_query($conn, "SELECT COUNT(*) AS selesai FROM laporan_mahasiswa WHERE user_id = $user_id AND nilai IS NOT NULL");
$selesai = mysqli_fetch_assoc($q2)['selesai'];

// Hitung tugas yang belum dinilai
$q3 = mysqli_query($conn, "SELECT COUNT(*) AS menunggu FROM laporan_mahasiswa WHERE user_id = $user_id AND nilai IS NULL");
$menunggu = mysqli_fetch_assoc($q3)['menunggu'];

// Ambil notifikasi terbaru (contoh: laporan terbaru)
$notif = mysqli_query($conn, "
    SELECT m.nama_modul, l.nilai, l.tanggal_upload 
    FROM laporan_mahasiswa l
    JOIN modul_praktikum m ON l.modul_id = m.id
    WHERE l.user_id = $user_id
    ORDER BY l.tanggal_upload DESC
    LIMIT 3
");
?>

<div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-3xl font-bold">Selamat Datang Kembali, <?= htmlspecialchars($_SESSION['nama']) ?>!</h1>
    <p class="mt-2 opacity-90">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-blue-600"><?= $praktikum ?></div>
        <div class="mt-2 text-lg text-gray-600">Praktikum Diikuti</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-green-500"><?= $selesai ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Selesai</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-yellow-500"><?= $menunggu ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Menunggu</div>
    </div>
    
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Notifikasi Terbaru</h3>
    <ul class="space-y-4">
        <?php if (mysqli_num_rows($notif) > 0): ?>
            <?php while ($n = mysqli_fetch_assoc($notif)): ?>
                <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                    <span class="text-xl mr-4">
                        <?= $n['nilai'] !== null ? '🔔' : '⏳' ?>
                    </span>
                    <div>
                        <?= $n['nilai'] !== null 
                            ? "Nilai untuk <span class='font-semibold text-blue-600'>" . htmlspecialchars($n['nama_modul']) . "</span> telah diberikan."
                            : "Menunggu penilaian untuk <span class='font-semibold text-blue-600'>" . htmlspecialchars($n['nama_modul']) . "</span>." ?>
                    </div>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li class="text-gray-500 italic">Belum ada laporan terkirim.</li>
        <?php endif; ?>
    </ul>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>