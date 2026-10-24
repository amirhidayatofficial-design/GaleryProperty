<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Komisi kini dibayar per termin (satu akad bisa punya beberapa baris komisi) dan
     * mengikuti state machine: pending → due → invoiced → paid → settled, atau void.
     */
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropUnique('commissions_digital_akad_id_unique');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->unsignedTinyInteger('termin')->default(1)->after('agency_partner_id');
            $table->decimal('termin_percentage', 5, 2)->default(100)->after('termin');
            $table->decimal('platform_fee_rate', 5, 2)->default(0)->after('total_commission');
            $table->string('invoice_number', 30)->nullable()->unique()->after('status');
            $table->timestamp('invoiced_at')->nullable()->after('invoice_number');
            $table->timestamp('due_at')->nullable()->after('invoiced_at');
            $table->timestamp('settled_at')->nullable()->after('payment_proof_path');
            $table->string('payout_proof_path', 255)->nullable()->after('settled_at');

            $table->unique(['digital_akad_id', 'termin']);
        });

        // Data lama (satu kali bayar) dijadikan termin tunggal 100% dan dipetakan ke status baru
        $sequence = [];
        foreach (DB::table('commissions')->orderBy('id')->get() as $row) {
            $changes = [
                'platform_fee_rate' => $row->total_commission > 0 ? round($row->platform_fee / $row->total_commission * 100, 2) : 0,
                'status' => match ($row->status) {
                    'paid' => 'settled',
                    'approved' => 'invoiced',
                    'cancelled' => 'void',
                    default => 'due',
                },
            ];

            if ($row->status === 'paid') {
                // Bukti transfer lama adalah bukti pembayaran ke agency
                $changes['settled_at'] = $row->paid_at ?? $row->updated_at;
                $changes['payout_proof_path'] = $row->payment_proof_path;
                $changes['payment_proof_path'] = null;
            }

            if ($row->status === 'approved') {
                $invoicedAt = \Illuminate\Support\Carbon::parse($row->updated_at);
                $prefix = 'INV/' . $invoicedAt->format('Ym') . '/';
                $sequence[$prefix] = ($sequence[$prefix] ?? 0) + 1;
                $changes['invoice_number'] = $prefix . str_pad((string) $sequence[$prefix], 4, '0', STR_PAD_LEFT);
                $changes['invoiced_at'] = $invoicedAt;
                $changes['due_at'] = $invoicedAt->copy()->addDays(14);
            }

            DB::table('commissions')->where('id', $row->id)->update($changes);
        }

        Schema::create('commission_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_id')->constrained('commissions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_status_logs');

        foreach (DB::table('commissions')->get() as $row) {
            DB::table('commissions')->where('id', $row->id)->update([
                'status' => match ($row->status) {
                    'settled' => 'paid',
                    'invoiced', 'paid' => 'approved',
                    'void' => 'cancelled',
                    default => 'pending',
                },
                'payment_proof_path' => $row->payout_proof_path ?? $row->payment_proof_path,
            ]);
        }

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropUnique(['digital_akad_id', 'termin']);
            $table->dropUnique(['invoice_number']);
            $table->dropColumn(['termin', 'termin_percentage', 'platform_fee_rate', 'invoice_number', 'invoiced_at', 'due_at', 'settled_at', 'payout_proof_path']);
        });

        // Catatan: gagal bila satu akad masih memiliki lebih dari satu termin
        Schema::table('commissions', function (Blueprint $table) {
            $table->unique('digital_akad_id');
        });
    }
};
