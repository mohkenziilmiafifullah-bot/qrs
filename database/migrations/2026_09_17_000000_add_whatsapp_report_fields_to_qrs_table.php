<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qrs', function (Blueprint $table) {
            // Merchant's WhatsApp number, stored normalized as 628xxxxxxxxxx so it can be
            // dropped straight into a wa.me link when sending the monthly report.
            $table->string('phone_number')->nullable()->after('google_place_id');

            // Last time the WhatsApp monthly analysis report was sent for this store.
            // Used to throttle the "Kirim Laporan WA" button to once every 30 days.
            $table->timestamp('last_report_sent_at')->nullable()->after('activated_at');
        });
    }

    public function down(): void
    {
        Schema::table('qrs', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'last_report_sent_at']);
        });
    }
};
