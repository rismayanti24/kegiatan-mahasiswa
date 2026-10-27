<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kegiatan</title>
</head>
<body>
    <h1>Edit Kegiatan</h1>

    @if ($errors->any())
        <p><strong>Periksa kembali isian Anda:</strong></p>
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('kegiatan.update', $kegiatan['id']) }}">
        @csrf
        @method('PUT')
        <p>
            <label>Kode Kegiatan<br>
                <input type="text" name="kode_kegiatan" value="{{ old('kode_kegiatan', $kegiatan['kode_kegiatan']) }}">
            </label>
        </p>
        <p>
            <label>Nama Kegiatan<br>
                <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan['nama_kegiatan']) }}">
            </label>
        </p>
        <p>
            <label>Email PIC<br>
                <input type="text" name="email_pic" value="{{ old('email_pic', $kegiatan['email_pic']) }}">
            </label>
        </p>
        <p>
            <label>Kuota (10-200)<br>
                <input type="text" name="kuota" value="{{ old('kuota', $kegiatan['kuota']) }}">
            </label>
        </p>
        <p>
            <label>Jenis Kegiatan<br>
                <select name="jenis_kegiatan_id">
                    <option value="">-- Pilih Jenis --</option>
                    @foreach ($daftarJenis as $j)
                        <option value="{{ $j['id'] }}"
                            @selected((string) old('jenis_kegiatan_id', $kegiatan['jenis_kegiatan_id']) === (string) $j['id'])>
                            {{ $j['nama_jenis'] }}
                        </option>
                    @endforeach
                </select>
            </label>
        </p>
        <button type="submit">Perbarui</button>
        <a href="{{ route('kegiatan.index') }}">Batal</a>
    </form>
</body>
</html>