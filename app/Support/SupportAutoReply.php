<?php

namespace App\Support;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;

/**
 * Balasan otomatis chat Bantuan. Kalimatnya dibuat bervariasi agar tidak terasa kaku.
 */
class SupportAutoReply
{
    /**
     * Sapaan pembuka di atas chat. ":name" diganti nama depan pengguna.
     */
    public const GREETINGS = [
        'Halo, :name! Ada yang bisa kami bantu hari ini?',
        "Assalamu'alaikum, :name. Silakan sampaikan pertanyaan atau kendala Anda, tim kami siap membantu.",
        'Hai :name, selamat datang di layanan Bantuan GalleryProperty. Ceritakan saja apa yang bisa kami bantu.',
        'Selamat datang, :name! Punya pertanyaan seputar akun, listing, atau transaksi? Tulis di sini, ya.',
    ];

    /**
     * Konfirmasi bahwa pesan sudah diterima dan admin akan segera membalas.
     */
    public const ACKNOWLEDGEMENTS = [
        'Terima kasih, pesan Anda sudah kami terima. Tim admin akan segera membalas, mohon ditunggu sebentar.',
        'Pertanyaan Anda sudah masuk ke tim kami. Mohon tunggu sebentar, admin akan merespons secepatnya.',
        'Pesan diterima! Admin kami sedang meninjau pertanyaan Anda dan akan membalas di percakapan ini.',
        'Terima kasih telah menghubungi kami. Mohon kesediaannya menunggu, tim support akan merespons sesegera mungkin.',
    ];

    /**
     * Sapaan tetap untuk satu pengguna dalam satu hari, supaya tidak berganti setiap kali chat diperbarui.
     */
    public static function greetingFor(User $user): string
    {
        $index = crc32($user->id . '|' . now()->toDateString()) % count(self::GREETINGS);

        return str_replace(':name', strtok($user->name, ' '), self::GREETINGS[$index]);
    }

    /**
     * Kirim konfirmasi otomatis bila pengguna baru mulai menunggu balasan:
     * pesan pertama, atau pesan pertama setelah admin membalas. Pesan lanjutan tidak dibalas ulang.
     */
    public static function acknowledge(SupportConversation $conversation, SupportMessage $userMessage): ?SupportMessage
    {
        $previous = $conversation->messages()
            ->with('sender')
            ->where('id', '<', $userMessage->id)
            ->latest('id')
            ->first();

        $waitingForAdmin = $previous && ! $previous->sender?->isAdmin();

        if ($waitingForAdmin) {
            return null;
        }

        // Hindari kalimat yang sama dengan konfirmasi otomatis sebelumnya
        $lastAcknowledgement = $conversation->messages()->whereNull('sender_id')->latest('id')->value('body');
        $choices = array_values(array_diff(self::ACKNOWLEDGEMENTS, [$lastAcknowledgement]));

        $reply = $conversation->messages()->create([
            'sender_id' => null,
            'body' => $choices[array_rand($choices)],
        ]);

        $conversation->update(['last_message_at' => $reply->created_at]);

        return $reply;
    }
}
