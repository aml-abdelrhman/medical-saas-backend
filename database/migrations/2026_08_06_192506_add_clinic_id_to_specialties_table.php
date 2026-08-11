<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إضافة عمود clinic_id لجدول specialties
        Schema::table('specialties', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->constrained('clinics')->cascadeOnDelete();
        });

        // 2. حذف الجدول الوسيط القديمclinic_specialty إن وجد لعدم الحاجة إليه
        Schema::dropIfExists('clinic_specialty');
    }

    public function down(): void
    {
        Schema::table('specialties', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });
    }
};