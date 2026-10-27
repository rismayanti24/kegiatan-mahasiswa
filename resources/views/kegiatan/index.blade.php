<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kegiatan Mahasiswa</title>
</head>
<body>
    <h1>Data Kegiatan Mahasiswa</h1>

    @if (session('sukses'))
        <p><strong>Berhasil:</strong> {{ session('sukses') }}</p>
    @endif

    @if (session('gagal'))
        <p><strong>Gagal:</strong> {{ session('gagal') }}</p>
    @endif

    @if (! empty($galat))
        <p><strong>Gagal:</strong> {{ $galat }}</p>
    @endif

    @if ($errors->any())
        <p><strong>Periksa kembali isian Anda:</strong></p>
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <h2>Tambah Kegiatan</h2>
    <form method="POST" action="{{ route('kegiatan.store') }}">
        @csrf
        <p>
            <label>Kode Kegiatan (10 karakter)<br>
                <input type="text" name="kode_kegiatan" value="{{ old('kode_kegiatan') }}">
            </label>
        </p>
        <p>
            <label>Nama Kegiatan<br>
                <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}">
            </label>
        </p>
        <p>
            <label>Email PIC<br>
                <input type="text" name="email_pic" value="{{ old('email_pic') }}">
            </label>
        </p>
        <p>
            <label>Kuota (10-200)<br>
                <input type="text" name="kuota" value="{{ old('kuota') }}">
            </label>
        </p>
        <p>
            <label>Jenis Kegiatan<br>
                <select name="jenis_kegiatan_id">
                    <option value="">-- Pilih Jenis --</option>
                    @foreach ($daftarJenis as $j)
                        <option value="{{ $j['id'] }}" @selected((string) old('jenis_kegiatan_id') === (string) $j['id'])>
                            {{ $j['nama_jenis'] }}
                        </option>
                    @endforeach
                </select>
            </label>
        </p>
        <button type="submit">Simpan</button>
    </form>

    <h2>Cari dan Filter</h2>
    <form method="GET" action="{{ route('kegiatan.index') }}">
        <label>Kata kunci (kode, nama, email):
            <input type="text" name="q" value="{{ $q }}">
        </label>
        <label>Jenis:
            <select name="jenis">
                <option value="">Semua Jenis</option>
                @foreach ($daftarJenis as $j)
                    <option value="{{ $j['id'] }}" @selected((string) $jenis === (string) $j['id'])>
                        {{ $j['nama_jenis'] }}
                    </option>
                @endforeach
            </select>
        </label>
        <button type="submit">Terapkan</button>
        <a href="{{ route('kegiatan.index') }}">Reset</a>
    </form>

    <h2>Daftar Kegiatan</h2>
    <p>Jumlah kegiatan: {{ count($kegiatan) }} | Total kuota: {{ $totalKuota }}</p>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Kegiatan</th>
                <th>Email PIC</th>
                <th>Kuota</th>
                <th>Status Kuota</th>
                <th>Jenis</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kegiatan as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['kode_kegiatan'] }}</td>
                    <td>{{ $row['nama_kegiatan'] }}</td>
                    <td>{{ $row['email_pic'] }}</td>
                    <td>{{ $row['kuota'] }}</td>
                    <td>{{ statusKuota((int) $row['kuota']) }}</td>
                    <td>{{ $row['nama_jenis'] }}</td>
                    <td>
                        <a href="{{ route('kegiatan.edit', $row['id']) }}">Edit</a>
                        <form method="POST" action="{{ route('kegiatan.destroy', $row['id']) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>