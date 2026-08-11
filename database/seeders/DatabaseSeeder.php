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
        Schema::disableForeignKeyConstraints();

        // 1. إنشاء حساب الـ Super Admin الخاص بالمنصة
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

        // 2. إنشاء الباقات الأساسية أولاً لكي يستطيع الـ ClinicSeeder استخدامها
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

        // 3. إضافة المستخدم التجريبي التقليدي إذا احتجت إليه
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'patient',
            ]
        );

        // 4. استدعاء باقي الـ Seeders بالترتيب
        $this->call([
            SpecialtySeeder::class,
            ClinicSeeder::class, // ستقوم بإنشاء العيادات وربطها باشتراكات نشطة تلقائياً
            DoctorSeeder::class,
            ServiceSeeder::class,
            AvailabilitySeeder::class,
            AppointmentSeeder::class,
            ClinicReviewSeeder::class, // إضافة هذا السطر لتوليد تقييمات الأطباء للمنصة
        ]);

        Schema::enableForeignKeyConstraints();
    }
}