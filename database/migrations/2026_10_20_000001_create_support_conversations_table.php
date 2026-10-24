<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('support_conversations', function (Blueprint $table) {
            $table->id();
            // Satu percakapan bantuan per pengguna (pembeli atau agency) dengan tim admin
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('user_last_read_message_id')->nullable();
            // Penanda baca bersama untuk seluruh admin
            $table->unsignedBigInteger('admin_last_read_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_conversations');
    }
};
