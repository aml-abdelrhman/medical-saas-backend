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
        if (!Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
                $table->json('name'); // {"ar": "...", "en": "..."}
                $table->string('image')->nullable(); // مسار الصورة
                $table->decimal('price', 8, 2);
                $table->integer('duration_minutes');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};