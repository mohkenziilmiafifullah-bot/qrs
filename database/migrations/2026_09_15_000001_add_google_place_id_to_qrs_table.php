<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qrs', function (Blueprint $table) {
            // Google Place ID used to build the "write a review" deep link
            // (https://search.google.com/local/writereview?placeid=...).
            // target_url is still kept as-is: it now stores the *generated*
            // review link when a place_id is set, or a manually pasted link
            // as a fallback for edge cases (e.g. no Google Business listing).
            $table->string('google_place_id')->nullable()->after('merchant_name');
        });
    }

    public function down(): void
    {
        Schema::table('qrs', function (Blueprint $table) {
            $table->dropColumn('google_place_id');
        });
    }
};
