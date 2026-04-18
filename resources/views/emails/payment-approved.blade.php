<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Disetujui</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:24px; color:#111827;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; padding:24px;">
        <h1 style="margin:0 0 12px; font-size:20px;">Pembayaran Disetujui</h1>
        <p style="margin:0 0 12px;">Pembayaran untuk invoice <strong>#{{ $invoice->id }}</strong> telah disetujui.</p>
        <p style="margin:0 0 8px; color:#4b5563;">Plan: {{ $invoice->plan?->name ?? '-' }}</p>
        <p style="margin:0 0 8px; color:#4b5563;">Periode: {{ $invoice->billing_period }}</p>
        <p style="margin:0; color:#4b5563;">Total: Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
    </div>
</body>
</html>
