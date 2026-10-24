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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agency_partner_id')->constrained('agency_partners')->cascadeOnDelete();
            $table->foreignId('property_project_id')->constrained('property_projects')->cascadeOnDelete();
            $table->unsignedBigInteger('buyer_last_read_message_id')->nullable();
            $table->unsignedBigInteger('agency_last_read_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();

            // Satu calon pembeli hanya punya satu percakapan per projek
            $table->unique(['buyer_id', 'property_project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
