<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SupportController;
use App\Models\SupportConversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Inbox customer support untuk admin: membalas pertanyaan pembeli dan agency partner.
 */
class SupportConversationController extends Controller
{
    public function index(Request $request): View
    {
        $roleFilter = $request->input('role');

        $conversations = SupportConversation::query()
            ->with(['user', 'latestMessage.sender'])
            ->when(in_array($roleFilter, ['buyer', 'agency_partner'], true), fn ($query) => $query
                ->whereHas('user', fn ($user) => $user->where('role', $roleFilter)))
            ->whereNotNull('last_message_at')
            ->orderByDesc('last_message_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.support.index', ['conversations' => $conversations, 'viewer' => $request->user()]);
    }

    public function show(Request $request, SupportConversation $supportConversation): View
    {
        $viewer = $request->user();
        $supportConversation->load('user');
        $messages = $supportConversation->messages()->with('sender')->oldest('id')->get();
        $supportConversation->markAsReadBy($viewer);

        return view('admin.support.show', ['conversation' => $supportConversation, 'messages' => $messages, 'viewer' => $viewer]);
    }

    public function messages(Request $request, SupportConversation $supportConversation): View
    {
        $viewer = $request->user();
        $messages = $supportConversation->messages()->with('sender')->oldest('id')->get();
        $supportConversation->markAsReadBy($viewer);

        return view('support._messages', compact('messages', 'viewer'));
    }

    public function send(Request $request, SupportConversation $supportConversation): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:' . SupportController::MAX_LENGTH],
        ]);

        SupportController::storeMessage($supportConversation, $request->user(), $validated['body']);

        return redirect()->route('admin.support.show', $supportConversation);
    }
}
