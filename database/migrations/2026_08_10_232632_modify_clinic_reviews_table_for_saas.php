<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinic_reviews', function (Blueprint $table) {
            // 1. فك قيد الإحالة (Foreign Key) الخاص بـ patient_id أولاً لتجنب خطأ قاعدة البيانات
            try {
                $table->dropForeign(['patient_id']);
            } catch (\Exception $e) {
                // تجاوز الخطأ في حال لم يكن موجوداً
            }

            // 2. فك قيد الإحالة الخاص بـ appointment_id إن وجد
            try {
                $table->dropForeign(['appointment_id']);
            } catch (\Exception $e) {
                // تجاوز الخطأ إن لم يكن موجوداً
            }

            // 3. حذف الأعمدة القديمة بأمان تام
            if (Schema::hasColumn('clinic_reviews', 'patient_id')) {
                $table->dropColumn('patient_id');
            }
            if (Schema::hasColumn('clinic_reviews', 'appointment_id')) {
                $table->dropColumn('appointment_id');
            }

            // 4. جعل clinic_id اختيارياً (nullable)
            if (Schema::hasColumn('clinic_reviews', 'clinic_id')) {
                $table->unsignedBigInteger('clinic_id')->nullable()->change();
            }

            // 5. إضافة الأعمدة الجديدة الخاصة بتقييمات الأطباء للمنصة
            if (!Schema::hasColumn('clinic_reviews', 'doctor_id')) {
                $table->unsignedBigInteger('doctor_id')->after('id');
            }
            if (!Schema::hasColumn('clinic_reviews', 'doctor_name')) {
                $table->string('doctor_name')->after('doctor_id');
            }
            if (!Schema::hasColumn('clinic_reviews', 'clinic_name')) {
                $table->string('clinic_name')->nullable()->after('doctor_name');
            }
            if (!Schema::hasColumn('clinic_reviews', 'doctor_avatar')) {
                $table->string('doctor_avatar')->nullable()->after('clinic_name');
            }
            if (!Schema::hasColumn('clinic_reviews', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('comment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinic_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->dropColumn(['doctor_id', 'doctor_name', 'clinic_name', 'doctor_avatar', 'is_approved']);
        });
    }
};