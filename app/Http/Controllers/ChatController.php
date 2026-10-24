<?php

namespace App\Http\Controllers;

use App\Models\BuyerLead;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PropertyProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Chat calon pembeli dengan agency partner (US-06).
 */
class ChatController extends Controller
{
    /**
     * Daftar percakapan milik user (pembeli atau agency).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = Conversation::forUser($user)
            ->with(['buyer', 'agencyPartner', 'propertyProject', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->latest()
            ->paginate(15);

        return view('chat.index', compact('conversations', 'user'));
    }

    /**
     * Pembeli memulai (atau melanjutkan) percakapan untuk sebuah projek.
     */
    public function start(Request $request, PropertyProject $propertyProject): RedirectResponse
    {
        abort_unless($request->user()->isBuyer(), 403, 'Hanya akun calon pembeli yang dapat memulai chat dengan agency.');
        abort_unless($propertyProject->is_verified && $propertyProject->agency_partner_id, 404);

        $conversation = Conversation::firstOrCreate(
            ['buyer_id' => $request->user()->id, 'property_project_id' => $propertyProject->id],
            ['agency_partner_id' => $propertyProject->agency_partner_id],
        );

        return redirect()->route('chat.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();
        abort_unless($conversation->isParticipant($user), 403);

        $conversation->load(['buyer', 'agencyPartner', 'propertyProject']);
        $messages = $conversation->messages()->with('sender')->oldest('id')->get();
        $conversation->markAsReadBy($user);

        return view('chat.show', compact('conversation', 'messages', 'user'));
    }

    /**
     * Potongan HTML daftar pesan, diambil berkala oleh halaman chat agar pesan baru muncul tanpa reload.
     */
    public function messages(Request $request, Conversation $conversation): View
    {
        $user = $request->user();
        abort_unless($conversation->isParticipant($user), 403);

        $messages = $conversation->messages()->with('sender')->oldest('id')->get();
        $conversation->markAsReadBy($user);

        return view('chat._messages', compact('messages', 'user'));
    }

    public function send(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();
        abort_unless($conversation->isParticipant($user), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:' . Message::MAX_LENGTH],
        ]);

        DB::transaction(function () use ($conversation, $user, $validated) {
            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => trim($validated['body']),
            ]);

            $conversation->update([
                'last_message_at' => $message->created_at,
                $conversation->readColumnFor($user) => $message->id,
            ]);

            if ($conversation->buyer_id === $user->id) {
                $this->ensureLeadExists($conversation);
            }
        });

        return redirect()->route('chat.show', $conversation);
    }

    /**
     * Pesan pertama pembeli otomatis tercatat sebagai lead baru untuk agency.
     */
    private function ensureLeadExists(Conversation $conversation): void
    {
        $buyer = $conversation->buyer;
        $project = $conversation->propertyProject;

        BuyerLead::firstOrCreate(
            ['user_id' => $buyer->id, 'property_project_id' => $project->id],
            [
                'agency_partner_id' => $conversation->agency_partner_id,
                'full_name' => $buyer->name,
                'phone' => $buyer->phone ?? '-',
                'email' => $buyer->email,
                'preferred_location' => $project->city,
                'preferred_scheme' => $project->syariah_scheme,
                'status' => 'new_lead',
                'notes' => 'Lead otomatis dari chat website.',
            ],
        );
    }
}
