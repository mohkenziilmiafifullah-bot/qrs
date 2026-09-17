<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive-only migration: introduces NFC tap tracking alongside the
     * existing QR-scan flow. Nothing already stored is touched — every
     * historical row backfills to 'qr' (its true origin), and the column
     * defaults to 'qr' so any code path that doesn't yet know about NFC
     * keeps behaving exactly as before.
     */
    public function up(): void
    {
        Schema::table('qr_scan_logs', function (Blueprint $table) {
            $table->string('source', 10)->default('qr')->after('browser');
        });
    }

    public function down(): void
    {
        Schema::table('qr_scan_logs', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
