<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDO;
use PDOException;
use Throwable;

class KegiatanController extends Controller
{
    private const PESAN_AMAN = 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.';

    /** Mengambil objek PDO dari koneksi Laravel. */
    private function pdo(): PDO
    {
        return DB::connection()->getPdo();
    }

    /** trim + strip_tags untuk semua input teks. */
    private function bersihkan(Request $request): array
    {
        $teks = fn ($nilai) => trim(strip_tags((string) $nilai));

        return [
            'kode_kegiatan'     => $teks($request->input('kode_kegiatan')),
            'nama_kegiatan'     => $teks($request->input('nama_kegiatan')),
            'email_pic'         => $teks($request->input('email_pic')),
            'kuota'             => $teks($request->input('kuota')),
            'jenis_kegiatan_id' => $teks($request->input('jenis_kegiatan_id')),
        ];
    }

    /** Menjalankan SELECT COUNT(*) dengan prepared statement. */
    private function sudahAda(string $sql, array $params): bool
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** Validasi server-side + pengecekan duplikat dan jenis (semua via PDO). */
    private function buatValidator(array $data, int $abaikanId = 0)
    {
        $validator = Validator::make($data, [
            'kode_kegiatan'     => ['required', 'regex:/^[A-Za-z0-9]{10}$/'],
            'nama_kegiatan'     => ['required', 'string', 'min:3', 'max:100'],
            'email_pic'         => ['required', 'email', 'max:100'],
            'kuota'             => ['required', 'integer', 'between:10,200'],
            'jenis_kegiatan_id' => ['required', 'integer'],
        ], [
            'kode_kegiatan.required'     => 'Kode kegiatan wajib diisi.',
            'kode_kegiatan.regex'        => 'Kode kegiatan harus tepat 10 karakter huruf/angka.',
            'nama_kegiatan.required'     => 'Nama kegiatan wajib diisi.',
            'nama_kegiatan.min'          => 'Nama kegiatan minimal 3 karakter.',
            'nama_kegiatan.max'          => 'Nama kegiatan maksimal 100 karakter.',
            'email_pic.required'         => 'Email penanggung jawab wajib diisi.',
            'email_pic.email'            => 'Format email tidak valid.',
            'email_pic.max'              => 'Email maksimal 100 karakter.',
            'kuota.required'             => 'Kuota wajib diisi.',
            'kuota.integer'              => 'Kuota harus berupa bilangan bulat.',
            'kuota.between'              => 'Kuota harus antara 10 sampai 200.',
            'jenis_kegiatan_id.required' => 'Jenis kegiatan wajib dipilih.',
            'jenis_kegiatan_id.integer'  => 'Jenis kegiatan tidak valid.',
        ]);

        $validator->after(function ($v) use ($data, $abaikanId) {
            $galat = $v->errors();

            if (! $galat->has('kode_kegiatan') && $this->sudahAda(
                'SELECT COUNT(*) FROM kegiatan WHERE kode_kegiatan = :kode AND id <> :id',
                [':kode' => $data['kode_kegiatan'], ':id' => $abaikanId]
            )) {
                $galat->add('kode_kegiatan', 'Kode kegiatan sudah digunakan.');
            }

            if (! $galat->has('email_pic') && $this->sudahAda(
                'SELECT COUNT(*) FROM kegiatan WHERE email_pic = :email AND id <> :id',
                [':email' => $data['email_pic'], ':id' => $abaikanId]
            )) {
                $galat->add('email_pic', 'Email penanggung jawab sudah digunakan.');
            }

            if (! $galat->has('jenis_kegiatan_id') && ! $this->sudahAda(
                'SELECT COUNT(*) FROM jenis_kegiatan WHERE id = :id',
                [':id' => $data['jenis_kegiatan_id']]
            )) {
                $galat->add('jenis_kegiatan_id', 'Jenis kegiatan tidak tersedia.');
            }
        });

        return $validator;
    }

    /** Daftar jenis untuk dropdown (tabel master). */
    private function ambilJenis(): array
    {
        return $this->pdo()
            ->query('SELECT id, nama_jenis FROM jenis_kegiatan ORDER BY nama_jenis')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---------- READ + PENCARIAN + FILTER ----------
    public function index(Request $request)
    {
        $q     = trim(strip_tags((string) $request->query('q', '')));
        $jenis = trim(strip_tags((string) $request->query('jenis', '')));

        $daftarJenis = [];
        $kegiatan    = [];
        $galat       = null;

        try {
            $daftarJenis = $this->ambilJenis();

            $sql = 'SELECT k.id, k.kode_kegiatan, k.nama_kegiatan, k.email_pic, k.kuota,
                           k.jenis_kegiatan_id, j.nama_jenis
                    FROM kegiatan k
                    JOIN jenis_kegiatan j ON j.id = k.jenis_kegiatan_id
                    WHERE 1 = 1';
            $params = [];

            if ($q !== '') {
                // Placeholder tidak boleh dipakai ulang (prepared statement native)
                $sql .= ' AND (k.kode_kegiatan LIKE :q1 OR k.nama_kegiatan LIKE :q2 OR k.email_pic LIKE :q3)';
                $like = '%' . $q . '%';
                $params[':q1'] = $like;
                $params[':q2'] = $like;
                $params[':q3'] = $like;
            }

            if ($jenis !== '' && ctype_digit($jenis)) {
                $sql .= ' AND k.jenis_kegiatan_id = :jenis';
                $params[':jenis'] = (int) $jenis;
            }

            $sql .= ' ORDER BY k.id ASC';

            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);
            $kegiatan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            report($e);
            $galat = 'Data tidak dapat ditampilkan saat ini. Silakan coba lagi nanti.';
        }

        $totalKuota = array_sum(array_column($kegiatan, 'kuota'));

        return view('kegiatan.index', compact('kegiatan', 'daftarJenis', 'q', 'jenis', 'galat', 'totalKuota'));
    }

    // ---------- CREATE ----------
    public function store(Request $request)
    {
        $data = $this->bersihkan($request);

        try {
            $validator = $this->buatValidator($data);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput($data);
            }

            $stmt = $this->pdo()->prepare(
                'INSERT INTO kegiatan (kode_kegiatan, nama_kegiatan, email_pic, kuota, jenis_kegiatan_id)
                 VALUES (:kode, :nama, :email, :kuota, :jenis)'
            );
            $stmt->execute([
                ':kode'  => $data['kode_kegiatan'],
                ':nama'  => $data['nama_kegiatan'],
                ':email' => $data['email_pic'],
                ':kuota' => (int) $data['kuota'],
                ':jenis' => (int) $data['jenis_kegiatan_id'],
            ]);

            return redirect()->route('kegiatan.index')->with('sukses', 'Data kegiatan berhasil ditambahkan.');
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) { // duplikat (balapan data)
                return redirect()->back()
                    ->withErrors(['kode_kegiatan' => 'Kode atau email sudah digunakan.'])
                    ->withInput($data);
            }
            report($e);
            return redirect()->route('kegiatan.index')->with('gagal', self::PESAN_AMAN);
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('kegiatan.index')->with('gagal', self::PESAN_AMAN);
        }
    }

    // ---------- FORM EDIT ----------
    public function edit($id)
    {
        try {
            $stmt = $this->pdo()->prepare(
                'SELECT id, kode_kegiatan, nama_kegiatan, email_pic, kuota, jenis_kegiatan_id
                 FROM kegiatan WHERE id = :id'
            );
            $stmt->execute([':id' => (int) $id]);
            $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $kegiatan) {
                return redirect()->route('kegiatan.index')->with('gagal', 'Data tidak ditemukan.');
            }

            $daftarJenis = $this->ambilJenis();

            return view('kegiatan.edit', compact('kegiatan', 'daftarJenis'));
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('kegiatan.index')->with('gagal', self::PESAN_AMAN);
        }
    }

    // ---------- UPDATE ----------
    public function update(Request $request, $id)
    {
        $id   = (int) $id;
        $data = $this->bersihkan($request);

        try {
            $validator = $this->buatValidator($data, $id);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput($data);
            }

            $stmt = $this->pdo()->prepare(
                'UPDATE kegiatan
                 SET kode_kegiatan = :kode, nama_kegiatan = :nama, email_pic = :email,
                     kuota = :kuota, jenis_kegiatan_id = :jenis
                 WHERE id = :id'
            );
            $stmt->execute([
                ':kode'  => $data['kode_kegiatan'],
                ':nama'  => $data['nama_kegiatan'],
                ':email' => $data['email_pic'],
                ':kuota' => (int) $data['kuota'],
                ':jenis' => (int) $data['jenis_kegiatan_id'],
                ':id'    => $id,
            ]);

            return redirect()->route('kegiatan.index')->with('sukses', 'Data kegiatan berhasil diperbarui.');
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                return redirect()->back()
                    ->withErrors(['kode_kegiatan' => 'Kode atau email sudah digunakan data lain.'])
                    ->withInput($data);
            }
            report($e);
            return redirect()->route('kegiatan.index')->with('gagal', self::PESAN_AMAN);
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('kegiatan.index')->with('gagal', self::PESAN_AMAN);
        }
    }

    // ---------- DELETE ----------
    public function destroy($id)
    {
        try {
            $stmt = $this->pdo()->prepare('DELETE FROM kegiatan WHERE id = :id');
            $stmt->execute([':id' => (int) $id]);

            if ($stmt->rowCount() === 0) {
                return redirect()->route('kegiatan.index')->with('gagal', 'Data tidak ditemukan.');
            }

            return redirect()->route('kegiatan.index')->with('sukses', 'Data kegiatan berhasil dihapus.');
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('kegiatan.index')->with('gagal', self::PESAN_AMAN);
        }
    }
}