CREATE DATABASE IF NOT EXISTS kegiatan_mahasiswa_2401020041
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kegiatan_mahasiswa_2401020041;

DROP TABLE IF EXISTS kegiatan;
DROP TABLE IF EXISTS jenis_kegiatan;

CREATE TABLE jenis_kegiatan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_jenis VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE kegiatan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_kegiatan CHAR(10) NOT NULL UNIQUE,
  nama_kegiatan VARCHAR(100) NOT NULL,
  email_pic VARCHAR(100) NOT NULL UNIQUE,
  kuota SMALLINT UNSIGNED NOT NULL,
  jenis_kegiatan_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_kuota CHECK (kuota BETWEEN 10 AND 200),
  CONSTRAINT fk_kegiatan_jenis FOREIGN KEY (jenis_kegiatan_id)
    REFERENCES jenis_kegiatan (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO jenis_kegiatan (nama_jenis) VALUES ('Akademik'), ('Sosial');

INSERT INTO kegiatan (kode_kegiatan, nama_kegiatan, email_pic, kuota, jenis_kegiatan_id) VALUES
('KG00000001', 'Pelatihan Web',   'web@kampus.ac.id',     40,  (SELECT id FROM jenis_kegiatan WHERE nama_jenis = 'Akademik')),
('KG00000002', 'Seminar Karier',  'karier@kampus.ac.id',  100, (SELECT id FROM jenis_kegiatan WHERE nama_jenis = 'Akademik')),
('KG00000003', 'Bakti Pesisir',   'pesisir@kampus.ac.id', 60,  (SELECT id FROM jenis_kegiatan WHERE nama_jenis = 'Sosial'));