<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// 2. Koneksi & Header
require_once '../config.php';
require_once 'templates/header.php';

// 3. Query Statistik
$q1 = mysqli_query($conn, "SELECT COUNT(*) AS total_modul FROM modul_praktikum");
$data1 = mysqli_fetch_assoc($q1);
$total_modul = $data1['total_modul'];

$q2 = mysqli_query($conn, "SELECT COUNT(*) AS total_laporan FROM laporan_mahasiswa");
$data2 = mysqli_fetch_assoc($q2);
$total_laporan = $data2['total_laporan'];

$q3 = mysqli_query($conn, "SELECT COUNT(*) AS belum_dinilai FROM laporan_mahasiswa WHERE nilai IS NULL");
$data3 = mysqli_fetch_assoc($q3);
$belum_dinilai = $data3['belum_dinilai'];

// 4. Query Aktivitas Terbaru
$q4 = mysqli_query($conn, "
    SELECT u.nama, m.nama_modul, l.tanggal_upload 
    FROM laporan_mahasiswa l 
    JOIN users u ON l.user_id = u.id 
    JOIN modul_praktikum m ON l.modul_id = m.id 
    ORDER BY l.tanggal_upload DESC 
    LIMIT 5
");
?>

<!-- Statistik -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-800"><?= $total_modul ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?= $total_laporan ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?= $belum_dinilai ?></p>
        </div>
    </div>
</div>

<!-- Aktivitas Terbaru -->
<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php while($row = mysqli_fetch_assoc($q4)): ?>
            <?php
            // Ambil inisial nama
            $inisial = strtoupper(substr($row['nama'], 0, 1)) . strtoupper(substr(explode(' ', $row['nama'])[1] ?? '', 0, 1));
            ?>
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                    <span class="font-bold text-gray-500"><?= $inisial ?></span>
                </div>
                <div>
                    <p class="text-gray-800">
                        <strong><?= htmlspecialchars($row['nama']) ?></strong> mengumpulkan laporan untuk 
                        <strong><?= htmlspecialchars($row['nama_modul']) ?></strong>
                    </p>
                    <p class="text-sm text-gray-500"><?= date("d M Y H:i", strtotime($row['tanggal_upload'])) ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php
// 5. Footer
require_once 'templates/footer.php';
?>
