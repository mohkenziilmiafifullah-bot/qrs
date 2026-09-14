<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qrs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->index();
            $table->foreignId('sales_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('merchant_name')->nullable();
            $table->text('target_url')->nullable();
            $table->enum('status', ['unassigned', 'active', 'suspended'])->default('unassigned');
            $table->string('batch_reference')->nullable()->index(); // groups QR codes printed together
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qrs');
    }
};
