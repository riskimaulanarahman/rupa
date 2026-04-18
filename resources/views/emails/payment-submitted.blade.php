<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Menunggu Verifikasi</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:24px; color:#111827;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden;">
        <div style="padding:24px; border-bottom:1px solid #f3f4f6;">
            <h1 style="margin:0; font-size:20px;">Pembayaran Tenant Menunggu Verifikasi</h1>
            <p style="margin:8px 0 0; color:#6b7280;">Invoice #{{ $invoice->id }}</p>
        </div>
        <div style="padding:24px;">
            <p style="margin-top:0;">Tenant <strong>{{ $invoice->tenant?->name ?? '-' }}</strong> telah mengirimkan bukti pembayaran.</p>
            <table style="width:100%; border-collapse:collapse; margin:16px 0;">
                <tr>
                    <td style="padding:6px 0; color:#6b7280;">Periode</td>
                    <td style="padding:6px 0; text-align:right;">{{ $invoice->billing_period }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0; color:#6b7280;">Plan</td>
                    <td style="padding:6px 0; text-align:right;">{{ $invoice->plan?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0; color:#6b7280;">Total</td>
                    <td style="padding:6px 0; text-align:right; font-weight:700;">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>

            @if($invoice->payment_note)
                <p style="margin:0 0 16px;">
                    <strong>Catatan tenant:</strong><br>
                    <span style="color:#4b5563;">{{ $invoice->payment_note }}</span>
                </p>
            @endif

            <div style="display:flex; gap:12px; margin-top:20px;">
                <a href="{{ $approveUrl }}" style="display:inline-block; background:#059669; color:#fff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:700;">Approve</a>
                <a href="{{ $rejectUrl }}" style="display:inline-block; background:#dc2626; color:#fff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:700;">Reject</a>
            </div>
        </div>
    </div>
</body>
</html>
