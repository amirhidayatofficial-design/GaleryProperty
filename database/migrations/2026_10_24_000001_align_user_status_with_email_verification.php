<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Selaraskan data lama dengan state machine akun: status active berarti email sudah terverifikasi.
     * Akun active yang emailnya belum terverifikasi dikembalikan ke pending.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('status', 'active')
            ->whereNull('email_verified_at')
            ->update(['status' => 'pending']);
    }

    /**
     * Data status sebelumnya tidak dapat dibedakan lagi, jadi rollback tidak mengubah apa pun.
     */
    public function down(): void
    {
        //
    }
};
