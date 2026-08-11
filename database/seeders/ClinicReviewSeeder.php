<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClinicReview;

class ClinicReviewSeeder extends Seeder
{
    public function run(): void
    {
        ClinicReview::truncate();
        $reviews = [
            [
                'doctor_id' => 23,
                'doctor_name' => 'د. عمر فاروق',
                'clinic_name' => 'عيادات الباطنة المتخصصة',
                'doctor_avatar' => 'doctors/doctor23.jpg',
                'rating' => 5,
                'comment' => 'أفضل استثمار قمت به لعيادتي، النظام وفر علي الكثير من الوقت والجهد، والدعم الفني ممتاز ويستجيب بسرعة.',
                'is_approved' => true,
            ],
            [
                'doctor_id' => 2,
                'doctor_name' => 'د. سارة خالد',
                'clinic_name' => 'مركز العيون الحديث',
                'doctor_avatar' => 'doctors/doctor2.jpg',
                'rating' => 5,
                'comment' => 'نظام سهل الاستخدام وفعال جداً، ساعدني على تنظيم مواعيد المرضى وتقليل نسبة الغيابات بشكل ملحوظ.',
                'is_approved' => true,
            ],
            [
                'doctor_id' => 12,
                'doctor_name' => 'د. محمد أحمد',
                'clinic_name' => 'عيادة الأسنان المتقدمة',
                'doctor_avatar' => 'doctors/doctor12.jpg',
                'rating' => 5,
                'comment' => 'منصة رائعة غيرت طريقة إدارتي للعيادة، الآن أستطيع التركيز على علاج المرضى بدلاً من الانشغال بالأمور الإدارية.',
                'is_approved' => true,
            ],
            [
                'doctor_id' => 4,
                'doctor_name' => 'د. مريم حسن',
                'clinic_name' => 'مجمّع الشفاء الطبي',
                'doctor_avatar' => 'doctors/doctor4.jpg',
                'rating' => 5,
                'comment' => 'التقارير الطبية وإدارة الملفات أصبحت أسهل بكثير، أنصح كل طبيب بتجربة هذه المنصة المتميزة.',
                'is_approved' => true,
            ],
            [
                'doctor_id' => 22,
                'doctor_name' => 'د. خالد عبد الله',
                'clinic_name' => 'عيادات الأطفال التخصصية',
                'doctor_avatar' => 'doctors/doctor5.jpg',
                'rating' => 5,
                'comment' => 'واجهة مستخدم احترافية وسلاسة عالية في التعامل مع السجلات الطبية. شكراً لفريق العمل على هذا الإبداع.',
                'is_approved' => true,
            ],
        ];

        foreach ($reviews as $review) {
            ClinicReview::create($review);
        }
    }
}