<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Isi email verifikasi akun (bahasa Indonesia)
        VerifyEmail::toMailUsing(fn (User $user, string $url) => (new MailMessage)
            ->subject('Verifikasi Email Akun GalleryProperty')
            ->greeting("Assalamu'alaikum, " . strtok($user->name, ' ') . '!')
            ->line('Satu langkah lagi untuk mengaktifkan akun GalleryProperty Anda.')
            ->line('Klik tombol di bawah ini untuk memverifikasi alamat email Anda. Setelah itu Anda dapat menggunakan seluruh fitur, termasuk chat dengan agency dan layanan Bantuan.')
            ->action('Verifikasi Alamat Email', $url)
            ->line('Tautan ini berlaku selama ' . config('auth.verification.expire', 60) . ' menit. Jika sudah kedaluwarsa, minta tautan baru dari halaman verifikasi.')
            ->line('Jika Anda tidak merasa mendaftar di GalleryProperty, abaikan email ini.'));
    }
}
