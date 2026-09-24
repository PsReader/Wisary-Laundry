<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 · Akses Ditolak</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:Inter,'Plus Jakarta Sans',system-ui,sans-serif;background:#F1F8FF;color:#0B1F3A;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem}
        .card{background:#fff;border:1px solid #E5E7EB;border-radius:1rem;padding:2.5rem;max-width:420px;width:100%;text-align:center;box-shadow:0 10px 30px -12px rgba(11,31,58,.12)}
        .code{font-size:3rem;font-weight:800;color:#1559A6;line-height:1}
        .title{font-size:1.125rem;font-weight:700;margin-top:1rem}
        .desc{font-size:.875rem;color:#52525B;margin-top:.5rem;line-height:1.5}
        .btn{display:inline-flex;align-items:center;justify-content:center;min-height:3rem;margin-top:1.5rem;padding:0 1.25rem;border-radius:.5rem;background:#1559A6;color:#fff;font-weight:600;font-size:.875rem;text-decoration:none}
    </style>
</head>
<body>
    <div class="card">
        <div class="code">403</div>
        <h1 class="title">Akses Ditolak</h1>
        <p class="desc">Halaman ini hanya dapat diakses oleh Super Admin. Silakan hubungi Super Admin jika Anda memerlukan akses.</p>
        <a class="btn" href="{{ url()->previous() ?: route('dashboard') }}">Kembali</a>
    </div>
</body>
</html>