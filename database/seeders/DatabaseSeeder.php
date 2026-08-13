<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // تعطيل قيود المفاتيح الأجنبية تماماً لمنع أي تداخل أو خطأ أثناء الـ Seeding
        Schema::disableForeignKeyConstraints();

        // 1. زرع التخصصات أولاً وبشكل مباشر لضمان وجودها قبل الكلينيك والدكاترة
        $this->call([
            SpecialtySeeder::class,
        ]);

        // 2. إنشاء حساب الـ Super Admin الخاص بالمنصة
        User::updateOrCreate(
            ['email' => 'superadmin@platform.com'],
            [
                'name' => 'Platform Super Admin',
                'phone' => '01000000000',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'clinic_id' => null,
            ]
        );

        // 3. إنشاء الباقات الأساسية
        Plan::updateOrCreate(
            ['price' => 0.00],
            [
                'name' => ['ar' => 'الباقة المجانية', 'en' => 'Free Plan'],
                'description' => ['ar' => 'باقة تجريبية للمنشآت الجديدة', 'en' => 'Trial plan for new clinics'],
                'price' => 0.00,
                'duration_in_days' => 14,
                'max_doctors' => 2,
                'max_patients' => 50,
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['price' => 299.99],
            [
                'name' => ['ar' => 'الباقة الاحترافية', 'en' => 'Professional Plan'],
                'description' => ['ar' => 'مناسبة للعيادات المتوسطة والنشطة', 'en' => 'Suitable for active medium clinics'],
                'price' => 299.99,
                'duration_in_days' => 30,
                'max_doctors' => 10,
                'max_patients' => 1000,
                'is_active' => true,
            ]
        );

        // 4. إضافة المستخدم التجريبي التقليدي
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'patient',
            ]
        );

        // 5. استدعاء باقي الـ Seeders بالترتيب السليم مع الحفاظ على تعطيل القيود
        $this->call([
            ClinicSeeder::class,
            DoctorSeeder::class,
            ServiceSeeder::class,
            AvailabilitySeeder::class,
            AppointmentSeeder::class,
            ClinicReviewSeeder::class,
        ]);

        // إعادة تفعيل قيود المفاتيح الأجنبية بعد الانتهاء تماماً
        Schema::enableForeignKeyConstraints();
    }
}