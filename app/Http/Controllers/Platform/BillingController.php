<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Mail\PaymentApprovedMail;
use App\Mail\PaymentRejectedMail;
use App\Models\OutletInvoice;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $allowedPeriods = ['this_month', 'last_month', 'this_quarter', 'this_year', 'all_time'];
        $allowedStatuses = ['pending', 'awaiting_verification', 'paid', 'overdue', 'cancelled'];

        $period = $request->string('period')->toString() ?: 'this_month';
        $status = $request->string('status')->toString();

        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'this_month';
        }

        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        [$startDate, $endDate] = $this->getPeriodDates($period);

        $invoices = OutletInvoice::with(['tenant', 'plan'])
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($period !== 'all_time', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $baseQuery = OutletInvoice::query()
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($period !== 'all_time', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });

        $totals = [
            'total_amount' => (int) (clone $baseQuery)->sum('total_amount'),
            'paid_amount' => (int) (clone $baseQuery)->where('status', 'paid')->sum('total_amount'),
            'pending_count' => (int) (clone $baseQuery)->where('status', 'pending')->count(),
            'awaiting_count' => (int) (clone $baseQuery)->where('status', 'awaiting_verification')->count(),
            'overdue_count' => (int) (clone $baseQuery)->where('status', 'overdue')->count(),
        ];

        $periods = [
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'this_quarter' => 'Kuartal Ini',
            'this_year' => 'Tahun Ini',
            'all_time' => 'Semua Waktu',
        ];

        return view('platform.billing.index', compact(
            'invoices',
            'totals',
            'period',
            'periods',
            'startDate',
            'endDate'
        ));
    }

    public function show(OutletInvoice $invoice): View
    {
        $invoice->load(['tenant', 'plan', 'approvedBy']);

        return view('platform.billing.show', compact('invoice'));
    }

    public function markPaid(OutletInvoice $invoice, BillingService $billingService): RedirectResponse
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice ini sudah berstatus lunas.');
        }

        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Invoice yang dibatalkan tidak dapat diproses sebagai lunas.');
        }

        $billingService->recordPayment($invoice);

        return back()->with('success', 'Invoice berhasil ditandai sebagai lunas.');
    }

    public function approve(OutletInvoice $invoice, BillingService $billingService): RedirectResponse
    {
        if (! in_array($invoice->status, ['awaiting_verification', 'pending', 'overdue'], true)) {
            return back()->with('error', 'Invoice ini tidak dapat di-approve.');
        }

        $this->approveInvoice($invoice, $billingService, auth()->id());

        return back()->with('success', 'Pembayaran berhasil di-approve.');
    }

    public function reject(Request $request, OutletInvoice $invoice): RedirectResponse
    {
        if (! in_array($invoice->status, ['awaiting_verification', 'pending', 'overdue'], true)) {
            return back()->with('error', 'Invoice ini tidak dapat di-reject.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->rejectInvoice($invoice, $validated['rejection_reason']);

        return back()->with('success', 'Pembayaran berhasil di-reject.');
    }

    public function approveViaEmail(Request $request, OutletInvoice $invoice, BillingService $billingService): Response
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Tautan verifikasi tidak valid atau sudah kedaluwarsa.');
        }

        $token = $request->query('token');

        return DB::transaction(function () use ($invoice, $token, $billingService): Response {
            $lockedInvoice = OutletInvoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedInvoice) {
                abort(404);
            }

            $tokenCheck = $this->validateEmailActionToken($lockedInvoice, 'approve', $token);
            if ($tokenCheck !== true) {
                return response()->view('platform.billing.email-action', [
                    'title' => 'Approve Gagal',
                    'message' => $tokenCheck,
                    'status' => 'error',
                ]);
            }

            if ($lockedInvoice->status !== 'paid') {
                $this->approveInvoice($lockedInvoice, $billingService, null, 'approve');
            } else {
                $lockedInvoice->update([
                    'approve_email_token' => null,
                    'reject_email_token' => null,
                    'approve_email_used_at' => now(),
                    'reject_email_used_at' => null,
                ]);
            }

            return response()->view('platform.billing.email-action', [
                'title' => 'Pembayaran Disetujui',
                'message' => 'Invoice berhasil di-approve melalui email.',
                'status' => 'success',
            ]);
        });
    }

    public function rejectViaEmail(Request $request, OutletInvoice $invoice): Response
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Tautan verifikasi tidak valid atau sudah kedaluwarsa.');
        }

        $token = $request->query('token');

        return DB::transaction(function () use ($invoice, $token): Response {
            $lockedInvoice = OutletInvoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedInvoice) {
                abort(404);
            }

            $tokenCheck = $this->validateEmailActionToken($lockedInvoice, 'reject', $token);
            if ($tokenCheck !== true) {
                return response()->view('platform.billing.email-action', [
                    'title' => 'Reject Gagal',
                    'message' => $tokenCheck,
                    'status' => 'error',
                ]);
            }

            if ($lockedInvoice->status !== 'paid') {
                $this->rejectInvoice($lockedInvoice, 'Pembayaran ditolak melalui tautan email superadmin.', 'reject');
            } else {
                $lockedInvoice->update([
                    'approve_email_token' => null,
                    'reject_email_token' => null,
                    'approve_email_used_at' => null,
                    'reject_email_used_at' => now(),
                ]);
            }

            return response()->view('platform.billing.email-action', [
                'title' => 'Pembayaran Ditolak',
                'message' => 'Invoice berhasil di-reject melalui email.',
                'status' => 'error',
            ]);
        });
    }

    private function getPeriodDates(string $period): array
    {
        return match ($period) {
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'all_time' => [now()->subYears(10), now()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function approveInvoice(
        OutletInvoice $invoice,
        BillingService $billingService,
        ?int $approvedBy,
        ?string $emailAction = null
    ): void {
        $invoice->loadMissing(['tenant', 'plan']);

        $billingService->recordPayment($invoice);
        $emailActionFields = $this->resolveEmailActionFields($emailAction);

        $invoice->refresh();
        $invoice->update([
            'status' => 'paid',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejected_at' => null,
            'rejection_reason' => null,
            'approve_email_token' => null,
            'reject_email_token' => null,
            'approve_email_used_at' => $emailActionFields['approve_email_used_at'],
            'reject_email_used_at' => $emailActionFields['reject_email_used_at'],
        ]);

        $this->sendApprovedEmail($invoice->fresh(['tenant', 'plan']));
    }

    private function rejectInvoice(OutletInvoice $invoice, string $reason, ?string $emailAction = null): void
    {
        $invoice->loadMissing(['tenant', 'plan']);
        $emailActionFields = $this->resolveEmailActionFields($emailAction);

        $invoice->update([
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'approve_email_token' => null,
            'reject_email_token' => null,
            'approve_email_used_at' => $emailActionFields['approve_email_used_at'],
            'reject_email_used_at' => $emailActionFields['reject_email_used_at'],
        ]);

        $this->sendRejectedEmail($invoice->fresh(['tenant', 'plan']));
    }

    private function sendApprovedEmail(OutletInvoice $invoice): void
    {
        $email = $invoice->tenant?->owner_email;
        if (! is_string($email) || $email === '') {
            return;
        }

        Mail::to($email)->send(new PaymentApprovedMail($invoice));
    }

    private function sendRejectedEmail(OutletInvoice $invoice): void
    {
        $email = $invoice->tenant?->owner_email;
        if (! is_string($email) || $email === '') {
            return;
        }

        Mail::to($email)->send(new PaymentRejectedMail($invoice));
    }

    private function validateEmailActionToken(OutletInvoice $invoice, string $action, mixed $token): bool|string
    {
        if (! is_string($token) || $token === '') {
            return 'Token verifikasi tidak ditemukan.';
        }

        $tokenField = $action === 'approve' ? 'approve_email_token' : 'reject_email_token';
        $usedAtField = $action === 'approve' ? 'approve_email_used_at' : 'reject_email_used_at';

        if (! is_string($invoice->{$tokenField}) || $invoice->{$tokenField} === '') {
            return 'Token verifikasi sudah tidak berlaku.';
        }

        if (! hash_equals((string) $invoice->{$tokenField}, $token)) {
            return 'Token verifikasi tidak valid.';
        }

        if ($invoice->{$usedAtField}) {
            return 'Tautan verifikasi sudah pernah digunakan.';
        }

        return true;
    }

    /**
     * @return array{approve_email_used_at: \Illuminate\Support\Carbon|null, reject_email_used_at: \Illuminate\Support\Carbon|null}
     */
    private function resolveEmailActionFields(?string $emailAction): array
    {
        return match ($emailAction) {
            'approve' => ['approve_email_used_at' => now(), 'reject_email_used_at' => null],
            'reject' => ['approve_email_used_at' => null, 'reject_email_used_at' => now()],
            default => ['approve_email_used_at' => null, 'reject_email_used_at' => null],
        };
    }
}
