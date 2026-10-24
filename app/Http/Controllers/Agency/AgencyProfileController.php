<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\AgencyPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Profil agency yang dikelola sendiri oleh agency partner.
 * Agency tanpa profil bisa mengisinya sendiri (menunggu verifikasi admin);
 * agency yang sudah punya profil bebas mengubahnya tanpa mengubah status verifikasi.
 */
class AgencyProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('agency.profile.edit', [
            'agencyPartner' => $request->user()->agencyPartner ?? new AgencyPartner,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(AgencyPartner::profileRules());
        $agencyPartner = $request->user()->agencyPartner;

        if ($agencyPartner) {
            $agencyPartner->update($validated);

            return redirect()->route('agency.profile.edit')->with('success', 'Profil agency berhasil diperbarui.');
        }

        $request->user()->agencyPartner()->create([
            ...$validated,
            'verification_status' => 'pending',
        ]);

        return redirect()
            ->route('agency.profile.edit')
            ->with('success', 'Profil agency berhasil dikirim dan menunggu verifikasi admin. Setelah terverifikasi, Anda dapat mengajukan listing.');
    }
}
