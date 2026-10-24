<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Komisi Saya: agency memantau status setiap termin komisinya secara transparan.
 */
class CommissionController extends Controller
{
    public function index(Request $request): View
    {
        $agencyPartner = $request->user()->agencyPartner;

        $commissions = $agencyPartner
            ? $agencyPartner->commissions()->with('digitalAkad.propertyProject')->latest('id')->paginate(10)
            : null;

        $summary = $agencyPartner ? [
            'settled' => $agencyPartner->commissions()->where('status', Commission::STATUS_SETTLED)->sum('net_commission'),
            'in_progress' => $agencyPartner->commissions()->whereIn('status', [Commission::STATUS_DUE, Commission::STATUS_INVOICED, Commission::STATUS_PAID])->sum('net_commission'),
            'pending' => $agencyPartner->commissions()->where('status', Commission::STATUS_PENDING)->sum('net_commission'),
        ] : null;

        return view('agency.commissions.index', compact('agencyPartner', 'commissions', 'summary'));
    }

    public function show(Request $request, Commission $commission): View
    {
        abort_unless($commission->agency_partner_id === $request->user()->agencyPartner?->id, 403);

        $commission->load(['digitalAkad.propertyProject', 'statusLogs']);

        return view('agency.commissions.show', compact('commission'));
    }
}
