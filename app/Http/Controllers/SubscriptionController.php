<?php

namespace App\Http\Controllers;

use App\Mail\PaymentSubmittedMail;
use App\Models\BankAccount;
use App\Models\OutletInvoice;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Show the expired / read-only message
     */
    public function expired(): View
    {
        $tenant = tenant();
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('subscription.expired', compact('tenant', 'plans'));
    }

    /**
     * Show billing & plans page for the owner
     */
    public function billing(): View
    {
        $tenant = tenant();
        $invoices = $tenant->invoices()->latest()->paginate(10);
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $bankAccounts = BankAccount::query()->where('is_active', true)->orderBy('bank_name')->get();

        return view('tenant.billing.index', compact('tenant', 'invoices', 'plans', 'bankAccounts'));
    }

    /**
     * Switch plan request
     */
    public function switchPlan(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenant = tenant();
        $plan = Plan::find($request->plan_id);

        // Simple update for now (in real app would trigger payment flow)
        $tenant->update(['plan_id' => $plan->id]);

        return back()->with('success', "Paket berhasil diubah ke {$plan->name}.");
    }

    public function submitPayment(Request $request, OutletInvoice $invoice): RedirectResponse
    {
        $tenant = tenant();
        if (! $tenant || (int) $invoice->tenant_id !== (int) $tenant->id) {
            abort(403);
        }

        if (! in_array($invoice->status, ['pending', 'overdue', 'awaiting_verification'], true)) {
            return back()->with('error', 'Invoice ini tidak dapat dikirimkan bukti pembayarannya.');
        }

        $validated = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'payment_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($invoice->payment_proof) {
            Storage::disk('public')->delete($invoice->payment_proof);
        }

        $proofPath = $request->file('payment_proof')->store("payment-proofs/tenant-{$tenant->id}", 'public');
        $approveEmailToken = (string) Str::uuid();
        $rejectEmailToken = (string) Str::uuid();

        $invoice->update([
            'status' => 'awaiting_verification',
            'payment_proof' => $proofPath,
            'payment_proof_at' => now(),
            'payment_note' => $validated['payment_note'] ?? null,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'approve_email_token' => $approveEmailToken,
            'reject_email_token' => $rejectEmailToken,
            'approve_email_used_at' => null,
            'reject_email_used_at' => null,
        ]);

        $invoice->load(['tenant', 'plan']);

        $superAdminEmails = User::query()
            ->where('role', 'superadmin')
            ->where('is_active', true)
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->values()
            ->all();

        if ($superAdminEmails !== []) {
            $approveUrl = URL::temporarySignedRoute(
                'platform.billing.approve-via-email',
                now()->addDays(2),
                ['invoice' => $invoice->id, 'token' => $approveEmailToken]
            );
            $rejectUrl = URL::temporarySignedRoute(
                'platform.billing.reject-via-email',
                now()->addDays(2),
                ['invoice' => $invoice->id, 'token' => $rejectEmailToken]
            );

            Mail::to($superAdminEmails)->send(new PaymentSubmittedMail($invoice, $approveUrl, $rejectUrl));
        }

        return back()->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi superadmin.');
    }
}
