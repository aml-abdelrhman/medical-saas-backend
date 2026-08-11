<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\ClinicSubscription;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ClinicSubscription::truncate();
        Clinic::truncate();
        Plan::truncate(); // تفريغ جدول الباقات لضمان إنشاء الـ 4 باقات الجديدة تماماً
        Schema::enableForeignKeyConstraints();

        // 1. إنشاء 4 باقات جديدة بالكامل مع الوصف التفصيلي (Description)
        $basicPlan = Plan::create([
            'name' => 'الباقة الأساسية',
            'price' => 199.00,
            'duration_in_days' => 30,
            'description' => 'مناسبة للعيادات والمراكز الصغيرة للبدء بإدارة العمل بشكل منظم وسهل، تتضمن إدارة المواعيد وتقارير المرضى الأساسية مع استخدام محدود للذكاء الاصطناعي.',
            'max_doctors' => 2,
            'max_patients' => 100,
        ]);

        $proPlan = Plan::create([
            'name' => 'الباقة الاحترافية',
            'price' => 499.00,
            'duration_in_days' => 30,
            'description' => 'للمراكز المتوسطة التي تحتاج عدد أطباء أكبر ودعم أقوى في التحليلات، مع صلاحيات أوسع وتقارير مرضى منظمة وواضحة ودعم فني متواصل.',
            'max_doctors' => 6,
            'max_patients' => 500,
        ]);

        $advancedPlan = Plan::create([
            'name' => 'الباقة المتقدمة',
            'price' => 1500.00,
            'duration_in_days' => 30,
            'description' => 'حل متكامل للمراكز الكبيرة التي تتطلب إمكانيات تشغيلية عالية، مع مميزات تحليلية متقدمة ومتابعة دقيقة للأداء المالي والإداري.',
            'max_doctors' => 15,
            'max_patients' => 2000,
        ]);

        $vipPlan = Plan::create([
            'name' => 'باقة الشركات (VIP)',
            'price' => 3500.00,
            'duration_in_days' => 365,
            'description' => 'حل شامل ومستدام للمستشفيات والمجمعات الطبية الكبرى التي تحتاج إمكانيات كاملة بدون أي قيود، مع ذكاء اصطناعي وتسجيل صوتي غير محدود واشتراك سنوي مميز.',
            'max_doctors' => 50,
            'max_patients' => 10000,
        ]);

        $plans = collect([$basicPlan, $proPlan, $advancedPlan, $vipPlan]);

        // 2. بيانات 5 عيادات مع مسار اللوجوهات الجديد: uploads/clinics-logos/
        $clinicsData = [
            [
                'name' => 'عيادات الشفاء التخصصية',
                'owner_name' => 'د. أحمد الشريف',
                'email' => 'info@alshefa.com',
                'phone' => '01012345678',
                'logo' => 'uploads/clinics-logos/shefa-logo.png',
                'status' => 'active',
                'sub_status' => 'active',
                'days_offset' => 0,
            ],
            [
                'name' => 'مجمع النور الطبي',
                'owner_name' => 'د. محمد النور',
                'email' => 'info@alnoor.com',
                'phone' => '01098765432',
                'logo' => 'uploads/clinics-logos/noor-logo.png',
                'status' => 'active',
                'sub_status' => 'trial',
                'days_offset' => -5,
            ],
            [
                'name' => 'مركز الأمل للأسنان',
                'owner_name' => 'د. محمود الأمل',
                'email' => 'contact@alamal-dental.com',
                'phone' => '01122334455',
                'logo' => 'uploads/clinics-logos/amal-logo.png',
                'status' => 'active',
                'sub_status' => 'expired',
                'days_offset' => -40,
            ],
            [
                'name' => 'عيادة الحياة لطب الأطفال',
                'owner_name' => 'د. سارة الحياة',
                'email' => 'support@alhayat-clinic.com',
                'phone' => '01233445566',
                'logo' => 'uploads/clinics-logos/hayat-logo.png',
                'status' => 'active',
                'sub_status' => 'active',
                'days_offset' => -10,
            ],
            [
                'name' => 'مركز الفيروز للرعاية الصحية',
                'owner_name' => 'د. خالد الفيروز',
                'email' => 'info@fayrouz-medical.com',
                'phone' => '01566778899',
                'logo' => 'uploads/clinics-logos/fayrouz-logo.png',
                'status' => 'active',
                'sub_status' => 'cancelled',
                'days_offset' => -20,
            ],
        ];

        foreach ($clinicsData as $index => $item) {
            $selectedPlan = $plans[$index % $plans->count()];

            $startsAt = now()->addDays($item['days_offset']);
            $endsAt = (clone $startsAt)->addDays($selectedPlan->duration_in_days ?? 30);

            // إنشاء العيادة
            $clinic = Clinic::create([
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'owner_name' => $item['owner_name'],
                'email' => $item['email'],
                'phone' => $item['phone'],
                'password' => Hash::make('12345678'),
                'logo' => $item['logo'],
                'status' => $item['status'],
                'subscription_ends_at' => $endsAt,
            ]);

            // إنشاء الاشتراك
            ClinicSubscription::create([
                'clinic_id' => $clinic->id,
                'plan_id' => $selectedPlan->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => $item['sub_status'],
            ]);
        }
    }
}