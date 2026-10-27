# Sistem Data Kegiatan Mahasiswa (Tipe D)

Praktikum Pemrograman Web (INF11197) - Laravel, PDO, MySQL/MariaDB.

- Nama: Rismayanti
- NIM: 2401020041
- Tipe soal: D (Kegiatan Mahasiswa)

## Deskripsi
Aplikasi CRUD untuk mencatat kegiatan mahasiswa berdasarkan jenis kegiatan,
lengkap dengan pencarian dan filter jenis kegiatan.

## Teknologi
Laravel, PHP, MySQL/MariaDB, PDO (prepared statement), Blade, Git dan GitHub.

## Struktur Database
- `jenis_kegiatan` (tabel master): id, nama_jenis
- `kegiatan` (tabel utama): id, kode_kegiatan, nama_kegiatan, email_pic,
  kuota, jenis_kegiatan_id, created_at, updated_at
- Relasi satu-ke-banyak: satu jenis kegiatan memiliki banyak kegiatan
  (ON UPDATE CASCADE, ON DELETE RESTRICT)

## Cara Menjalankan
1. Clone repository, lalu masuk ke foldernya
2. `composer install`
3. Salin `.env.example` menjadi `.env`, lalu isi konfigurasi database
4. `php artisan key:generate`
5. Impor `database/sql/kegiatan_mahasiswa.sql` ke MySQL
6. `composer dump-autoload`
7. `php artisan serve`
8. Buka http://127.0.0.1:8000/kegiatan

## Route
| Metode | URL | Fungsi |
|--------|-----|--------|
| GET | /kegiatan | Daftar, pencarian, filter |
| POST | /kegiatan | Tambah data |
| GET | /kegiatan/{id}/edit | Form edit |
| PUT | /kegiatan/{id} | Simpan perubahan |
| DELETE | /kegiatan/{id} | Hapus data |

## Keamanan
- Semua query memakai prepared statement PDO
- Input dibersihkan dengan trim dan strip_tags, divalidasi di server
- Token CSRF pada semua form
- Kesalahan dicatat lewat report(), pengguna hanya melihat pesan aman
- File .env tidak masuk repository