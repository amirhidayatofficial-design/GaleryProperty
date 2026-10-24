<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Support\SupportAutoReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Chat customer support (Bantuan) dari sisi pembeli dan agency partner ke tim admin.
 */
class SupportController extends Controller
{
    public const MAX_LENGTH = 1000;

    public function show(Request $request): View
    {
        $user = $request->user();
        $conversation = SupportConversation::firstWhere('user_id', $user->id);
        $messages = $conversation?->messages()->with('sender')->oldest('id')->get() ?? collect();
        $conversation?->markAsReadBy($user);

        return view('support.show', compact('conversation', 'messages', 'user'));
    }

    /**
     * Potongan HTML daftar pesan, diambil berkala agar balasan admin muncul tanpa reload.
     */
    public function messages(Request $request): View
    {
        $user = $request->user();
        $conversation = SupportConversation::firstWhere('user_id', $user->id);
        $messages = $conversation?->messages()->with('sender')->oldest('id')->get() ?? collect();
        $conversation?->markAsReadBy($user);

        return view('support._messages', ['messages' => $messages, 'viewer' => $user]);
    }

    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:' . self::MAX_LENGTH],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $conversation = SupportConversation::firstOrCreate(['user_id' => $user->id]);
            $message = static::storeMessage($conversation, $user, $validated['body']);
            SupportAutoReply::acknowledge($conversation, $message);
        });

        return redirect()->route('support.show');
    }

    /**
     * Simpan pesan dan tandai sudah dibaca oleh pengirimnya. Dipakai juga oleh sisi admin.
     */
    public static function storeMessage(SupportConversation $conversation, User $sender, string $body): SupportMessage
    {
        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => trim($body),
        ]);

        $conversation->update([
            'last_message_at' => $message->created_at,
            SupportConversation::readColumnFor($sender) => $message->id,
        ]);

        return $message;
    }
}
