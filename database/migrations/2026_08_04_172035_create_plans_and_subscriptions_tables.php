<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. جدول الباقات (مثل: الباقة المجانية، الباقة الاحترافية، الباقة الشاملة)
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // اسم الباقة متعدد اللغات ['ar' => '...', 'en' => '...']
            $table->json('description')->nullable(); // وصف الباقة
            $table->decimal('price', 8, 2)->default(0); // سعر الباقة
            $table->integer('duration_in_days')->default(30); // مدة الباقة بالأيام (مثلاً 30 أو 365)
            $table->integer('max_doctors')->default(5); // الحد الأقصى للأطباء المسموح بهم في هذه الباقة
            $table->integer('max_patients')->default(500); // الحد الأقصى للمرضى
            $table->boolean('is_active')->default(true); // حالة تفعيل الباقة
            $table->timestamps();
        });

        // 2. جدول اشتراكات العيادات (يربط كل عيادة بالباقة المشتركة بها وتواريخ البدء والانتهاء)
        Schema::create('clinic_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->date('starts_at'); // تاريخ بداية الاشتراك
            $table->date('ends_at'); // تاريخ انتهاء الاشتراك
            $table->enum('status', ['active', 'expired', 'cancelled', 'trial'])->default('trial'); // حالة الاشتراك
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_subscriptions');
        Schema::dropIfExists('plans');
    }
};