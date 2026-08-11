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
      Schema::create('clinics', function (Blueprint $table) {
    $table->id();
    $table->json('name'); // اسم العيادة (يدعم لغات متعددة)
    $table->string('slug')->unique(); // رابط فريد للعيادة (مثلا: /clinics/el-shefaa)
    $table->string('email')->unique();
    $table->string('phone')->nullable();
    $table->string('logo')->nullable();
    $table->enum('status', ['active', 'suspended', 'pending'])->default('pending'); // حالة الاشتراك
    $table->timestamp('subscription_ends_at')->nullable(); // تاريخ انتهاء الاشتراك
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
