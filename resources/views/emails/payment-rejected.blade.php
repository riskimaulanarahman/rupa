<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Ditolak</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:24px; color:#111827;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; padding:24px;">
        <h1 style="margin:0 0 12px; font-size:20px;">Pembayaran Ditolak</h1>
        <p style="margin:0 0 12px;">Pembayaran untuk invoice <strong>#{{ $invoice->id }}</strong> belum dapat kami setujui.</p>
        <p style="margin:0 0 8px; color:#4b5563;">Alasan: {{ $invoice->rejection_reason ?? 'Perlu verifikasi ulang.' }}</p>
        <p style="margin:0; color:#4b5563;">Silakan upload ulang bukti pembayaran yang valid dari halaman billing tenant.</p>
    </div>
</body>
</html>
