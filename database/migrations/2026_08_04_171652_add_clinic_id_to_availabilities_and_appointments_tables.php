<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إضافة clinic_id لجدول أوقات دوام الأطباء
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained('clinics')->cascadeOnDelete();
        });

        // 2. إضافة clinic_id لجدول المواعيد والحجوزات
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained('clinics')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });
    }
};