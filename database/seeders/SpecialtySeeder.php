<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialty;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Specialty::truncate();
        Schema::enableForeignKeyConstraints();

        $specialties = [
            [
                'name' => ['ar' => 'تجميل', 'en' => 'Aesthetics'],
                'slug' => 'aesthetics',
                'description' => ['ar' => 'نقدم حلولاً متطورة للعناية بالبشرة والجسم، تشمل علاجات التجديد الشامل، شد الوجه غير الجراحي، وبرامج العناية بالجمال ونحت القوام باستخدام أحدث الأجهزة والتقنيات العالمية المعتمدة.', 'en' => 'Offering advanced skin care solutions, including comprehensive rejuvenation treatments, non-surgical facelifts, and beauty care programs.'],
            ],
            [
                'name' => ['ar' => 'أسنان', 'en' => 'Dentistry'],
                'slug' => 'dentistry',
                'description' => ['ar' => 'رعاية شاملة ومتكاملة لصحة الفم والأسنان، بدءاً من الفحوصات الدورية الدقيقة والحشوات التجميلية، وصولاً إلى زراعة الأسنان وتقويمها بأعلى معايير الجودة الطبية والتعقيم.', 'en' => 'Comprehensive dental care, ranging from routine check-ups and fillings to advanced cosmetic dentistry and orthodontics.'],
            ],
            [
                'name' => ['ar' => 'تغذية', 'en' => 'Nutrition'],
                'slug' => 'nutrition',
                'description' => ['ar' => 'تصميم خطط غذائية علاجية ورياضية مخصصة لإنقاص الوزن أو اكتسابه، وعلاجات التغذية السريرية المتقدمة للمساعدة في إدارة الأمراض المزمنة وصحة الأجسام بأسلوب حياة صحي ومستدام.', 'en' => 'Designing customized diet plans for weight management and clinical nutrition therapies to help manage chronic diseases.'],
            ],
            [
                'name' => ['ar' => 'نساء وتوليد', 'en' => 'OB/GYN'],
                'slug' => 'ob-gyn',
                'description' => ['ar' => 'رعاية طبية فائقة ومتكاملة لصحة المرأة في كافة المراحل، تشمل متابعة الحمل الطبيعي وعالي الخطورة، الولادة الآمنة، صحة الجهاز التناسلي، وتقنيات الإنجاب المساعدة.', 'en' => 'Integrated medical care for women, including pregnancy monitoring, childbirth, reproductive health, and specialized consultations.'],
            ],
            [
                'name' => ['ar' => 'علاج طبيعي', 'en' => 'Physical Therapy'],
                'slug' => 'physical-therapy',
                'description' => ['ar' => 'برامج تأهيل حركي وعصبي متقدمة لعلاج إصابات الملاعب، آلام الظهر الحادة والمفاصل، وإعادة التأهيل الشامل ما بعد الجراحات لاستعادة القوة الكاملة والوظائف الحركية للجسم.', 'en' => 'Advanced rehabilitation programs to treat sports injuries, back and joint pain, and post-surgical recovery.'],
            ],
            [
                'name' => ['ar' => 'نفسية', 'en' => 'Psychiatry'],
                'slug' => 'psychiatry',
                'description' => ['ar' => 'توفير بيئة علاجية آمنة، سرية وداعمة للصحة النفسية والعاطفية، تشمل التشخيص الدقيق والعلاج النفسي والسلوكي لمختلف الاضطرابات لتعزيز الاستقرار الذهني وجودة الحياة.', 'en' => 'Providing a safe and supportive environment for mental health, including diagnosis and psychological therapy for various disorders.'],
            ],
        ];

        foreach ($specialties as $index => $item) {
            $imageNumber = $index + 1;

            Specialty::create([
                'name' => $item['name'],
                'slug' => $item['slug'],
                'description' => $item['description'],
                'image' => "specialties/specialty{$imageNumber}.jpg",
            ]);
        }
    }
}