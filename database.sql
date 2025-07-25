CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE praktikum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_praktikum VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    semester VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE praktikum_mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    praktikum_id INT NOT NULL,
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, praktikum_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (praktikum_id) REFERENCES praktikum(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE modul_praktikum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    praktikum_id INT NOT NULL,
    nama_modul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    file_materi VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (praktikum_id) REFERENCES praktikum(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE laporan_mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    modul_id INT NOT NULL,
    file_laporan VARCHAR(255),
    nilai INT DEFAULT NULL,
    feedback TEXT DEFAULT NULL,
    tanggal_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (modul_id) REFERENCES modul_praktikum(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


