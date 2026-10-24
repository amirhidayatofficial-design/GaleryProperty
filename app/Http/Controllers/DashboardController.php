<?php

namespace App\Http\Controllers;

use App\Models\AgencyPartner;
use App\Models\BuyerLead;
use App\Models\Commission;
use App\Models\Conversation;
use App\Models\PropertyProject;
use App\Models\SupportConversation;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard sesuai peran: ringkasan angka dan pintasan ke pekerjaan yang perlu ditindaklanjuti.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $cards = [
                ['label' => 'Listing menunggu verifikasi', 'value' => PropertyProject::where('is_verified', false)->count(), 'url' => route('admin.property-projects.index'), 'accent' => true],
                ['label' => 'Agency menunggu verifikasi', 'value' => AgencyPartner::where('verification_status', 'pending')->count(), 'url' => route('admin.agency-partners.index', ['verification_status' => 'pending']), 'accent' => true],
                ['label' => 'Lead baru', 'value' => BuyerLead::where('status', 'new_lead')->count(), 'url' => route('admin.buyer-leads.index', ['status' => 'new_lead'])],
                ['label' => 'Komisi belum dibayar', 'value' => Commission::whereIn('status', ['pending', 'approved'])->count(), 'url' => route('admin.commissions.index')],
                ['label' => 'Pesan bantuan belum dibaca', 'value' => SupportConversation::unreadConversationCountFor($user), 'url' => route('admin.support.index'), 'accent' => true],
            ];
            $actions = [
                ['label' => 'Verifikasi listing', 'url' => route('admin.property-projects.index')],
                ['label' => 'Terbitkan akad', 'url' => route('admin.digital-akads.create')],
                ['label' => 'Kelola pengguna', 'url' => route('admin.users.index')],
            ];
        } elseif ($user->isAgencyPartner()) {
            $agency = $user->agencyPartner;
            $cards = [
                ['label' => 'Chat belum dibaca', 'value' => Conversation::unreadConversationCountFor($user), 'url' => route('chat.index'), 'accent' => true],
                ['label' => 'Listing tayang', 'value' => $agency?->propertyProjects()->where('is_verified', true)->count() ?? 0, 'url' => route('agency.property-projects.index')],
                ['label' => 'Listing menunggu verifikasi', 'value' => $agency?->propertyProjects()->where('is_verified', false)->count() ?? 0, 'url' => route('agency.property-projects.index')],
                ['label' => 'Komisi bersih dibayar', 'value' => 'Rp ' . number_format($agency?->commissions()->where('status', 'paid')->sum('net_commission') ?? 0, 0, ',', '.')],
            ];
            $actions = $agency?->isVerified()
                ? [['label' => 'Ajukan listing baru', 'url' => route('agency.property-projects.create')], ['label' => 'Buka chat', 'url' => route('chat.index')]]
                : [['label' => 'Lihat status akun agency', 'url' => route('agency.property-projects.index')]];
        } else {
            $cards = [
                ['label' => 'Chat belum dibaca', 'value' => Conversation::unreadConversationCountFor($user), 'url' => route('chat.index'), 'accent' => true],
                ['label' => 'Percakapan dengan agency', 'value' => Conversation::forUser($user)->count(), 'url' => route('chat.index')],
                ['label' => 'Properti tersedia di katalog', 'value' => PropertyProject::published()->count(), 'url' => route('catalog.index')],
            ];
            $actions = [
                ['label' => 'Jelajahi katalog', 'url' => route('catalog.index')],
                ['label' => 'Buka chat', 'url' => route('chat.index')],
            ];
        }

        return view('dashboard', compact('user', 'cards', 'actions'));
    }
}
