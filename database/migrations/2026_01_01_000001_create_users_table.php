<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['admin', 'sales'])->default('sales')->after('password');
            $table->decimal('wallet_balance', 12, 2)->default(0)->after('role');
            $table->enum('status', ['pending', 'active', 'blocked'])->default('active')->after('wallet_balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'wallet_balance', 'status']);
        });
    }
};