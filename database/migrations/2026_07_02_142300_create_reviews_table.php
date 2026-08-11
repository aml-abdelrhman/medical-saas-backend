<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('reviews', function (Blueprint $table) {
        $table->id();
        $table->foreignId('patient_id')->constrained('users');
        $table->foreignId('doctor_id')->constrained('doctors');
        $table->foreignId('appointment_id')->unique()->constrained('appointments'); // unique عشان الموعد يتقيم مرة واحدة
        $table->integer('rating'); // 1-5
        $table->json('comment')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
