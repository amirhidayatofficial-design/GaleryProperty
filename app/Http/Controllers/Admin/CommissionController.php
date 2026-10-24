<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

/**
 * Siklus komisi per termin. Komisi tidak diisi manual: diterbitkan otomatis dari akad,
 * lalu admin menjalankan setiap transisi lewat tombol sesuai state machine.
 */
class CommissionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');

        $commissions = Commission::query()
            ->with(['digitalAkad.propertyProject', 'agencyPartner'])
            ->when($search = $request->input('search'), fn ($query) => $query->where(fn ($q) => $q
                ->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('digitalAkad', fn ($a) => $a->where('akad_number', 'like', "%{$search}%"))
                ->orWhereHas('agencyPartner', fn ($a) => $a->where('agency_name', 'like', "%{$search}%"))))
            ->when($status === 'overdue', fn ($query) => $query->overdue())
            ->when(array_key_exists($status, Commission::STATUSES), fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $counts = Commission::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $overdueCount = Commission::overdue()->count();

        return view('admin.commissions.index', compact('commissions', 'counts', 'overdueCount', 'status'));
    }

    public function show(Commission $commission): View
    {
        $commission->load(['digitalAkad.propertyProject', 'digitalAkad.buyerLead', 'agencyPartner', 'statusLogs.user']);

        return view('admin.commissions.show', compact('commission'));
    }

    /**
     * Cetak/simpan invoice ke developer (developer tidak memiliki akun).
     */
    public function invoice(Commission $commission): View
    {
        abort_unless($commission->invoice_number, 404);
        $commission->load(['digitalAkad.propertyProject', 'digitalAkad.buyerLead', 'agencyPartner']);

        return view('admin.commissions.invoice', compact('commission'));
    }

    /**
     * pending → due: syarat termin terpenuhi (mis. DP lunas, serah terima).
     */
    public function markDue(Request $request, Commission $commission): RedirectResponse
    {
        $validated = $request->validate(['note' => ['required', 'string', 'max:500']], [], ['note' => 'bukti / keterangan syarat']);

        return $this->transition($commission, fn () => $commission->transitionTo(
            Commission::STATUS_DUE, $request->user(), $validated['note']
        ), 'Syarat termin terpenuhi. Komisi siap ditagihkan ke developer.');
    }

    /**
     * due → invoiced: invoice bernomor unik diterbitkan dengan jatuh tempo.
     */
    public function issueInvoice(Request $request, Commission $commission): RedirectResponse
    {
        return $this->transition($commission, function () use ($request, $commission) {
            $number = Commission::nextInvoiceNumber();
            $commission->transitionTo(Commission::STATUS_INVOICED, $request->user(), "Invoice {$number} diterbitkan.", [
                'invoice_number' => $number,
                'invoiced_at' => now(),
                'due_at' => now()->addDays((int) config('commission.invoice_due_days'))->endOfDay(),
            ]);
        }, 'Invoice diterbitkan. Cetak dan kirimkan ke developer.');
    }

    /**
     * invoiced → paid: dana komisi dari developer diterima di rekening platform.
     */
    public function recordPayment(Request $request, Commission $commission): RedirectResponse
    {
        $validated = $request->validate([
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [], ['paid_at' => 'tanggal diterima', 'payment_proof' => 'bukti pembayaran developer']);

        return $this->transition($commission, fn () => $commission->transitionTo(
            Commission::STATUS_PAID, $request->user(), 'Dana komisi dari developer diterima.', [
                'paid_at' => $validated['paid_at'],
                'payment_proof_path' => $request->file('payment_proof')->store('receipts/commissions', 'public'),
            ]
        ), 'Pembayaran developer tercatat. Teruskan bagian agency paling lambat ' . config('commission.payout_due_business_days') . ' hari kerja.');
    }

    /**
     * paid → settled: bagian agency diteruskan (amanah wakil). Status final.
     */
    public function recordPayout(Request $request, Commission $commission): RedirectResponse
    {
        $request->validate([
            'payout_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [], ['payout_proof' => 'bukti transfer ke agency']);

        return $this->transition($commission, fn () => $commission->transitionTo(
            Commission::STATUS_SETTLED, $request->user(), 'Bagian agency diteruskan ke rekening ' . ($commission->agencyPartner?->bank_name ?? 'agency') . '.', [
                'settled_at' => now(),
                'payout_proof_path' => $request->file('payout_proof')->store('receipts/payouts', 'public'),
            ]
        ), 'Payout ke agency tercatat. Termin komisi selesai.');
    }

    /**
     * pending → void: termin gugur karena akad batal sebelum syaratnya terpenuhi.
     */
    public function void(Request $request, Commission $commission): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']], [], ['reason' => 'alasan']);

        return $this->transition($commission, fn () => $commission->transitionTo(
            Commission::STATUS_VOID, $request->user(), $validated['reason']
        ), 'Termin komisi digugurkan.');
    }

    private function transition(Commission $commission, Closure $action, string $successMessage): RedirectResponse
    {
        try {
            $action();
        } catch (LogicException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.commissions.show', $commission)->with('success', $successMessage);
    }
}
