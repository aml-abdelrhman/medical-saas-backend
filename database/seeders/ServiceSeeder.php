<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('services')->truncate();
        Schema::enableForeignKeyConstraints();

        $servicesCatalog = [
            'plastic' => [
                [
                    'name' => ['ar' => 'حقن فيلر للوجه', 'en' => 'Facial Filler Injection'],
                    'description' => ['ar' => 'حقن مواد مالئة لتعبئة التجاعيد وإعادة الحيوية والامتلاء للوجه.', 'en' => 'Injecting fillers to smooth wrinkles and restore facial volume.'],
                    'price' => 2500, 'duration' => 45, 'image' => 'services/filler.jpg'
                ],
                [
                    'name' => ['ar' => 'حقن بوتكس', 'en' => 'Botox Injection'],
                    'description' => ['ar' => 'إزالة التجاعيد التعبيرية وشد البشرة بمنطقة الجبهة حول العينين.', 'en' => 'Smoothing expression lines and tightening forehead and eye areas.'],
                    'price' => 1800, 'duration' => 30, 'image' => 'services/botox.jpg'
                ],
                [
                    'name' => ['ar' => 'جلسة ليزر تفتيح', 'en' => 'Laser Skin Whitening'],
                    'description' => ['ar' => 'توحيد لون البشرة وإزالة التصبغات باستخدام تقنية الليزر الحديثة.', 'en' => 'Even out skin tone and remove pigmentation using modern laser technology.'],
                    'price' => 900, 'duration' => 40, 'image' => 'services/laser.jpg'
                ],
                [
                    'name' => ['ar' => 'نحت الجسم بالليزر', 'en' => 'Laser Body Contouring'],
                    'description' => ['ar' => 'إذابة الدهون المتراكمة ونحت القوام بدقة عالية.', 'en' => 'Dissolving stubborn fat and contouring the body with high precision.'],
                    'price' => 4500, 'duration' => 90, 'image' => 'services/body-contour.jpg'
                ],
                [
                    'name' => ['ar' => 'شد الوجه بالخيوط', 'en' => 'Thread Face Lift'],
                    'description' => ['ar' => 'شد الترهلات وتحفيز إنتاج الكولاجين بدون جراحة.', 'en' => 'Lifting sagging skin and stimulating collagen production without surgery.'],
                    'price' => 5000, 'duration' => 60, 'image' => 'services/thread-lift.jpg'
                ],
                [
                    'name' => ['ar' => 'تقشير كيميائي للبشرة', 'en' => 'Chemical Peeling'],
                    'description' => ['ar' => 'تجديد خلايا البشرة وإزالة الخلايا الميتة والآثار.', 'en' => 'Renewing skin cells, removing dead skin and blemishes.'],
                    'price' => 1200, 'duration' => 45, 'image' => 'services/peeling.jpg'
                ],
            ],
            'nutrition' => [
                [
                    'name' => ['ar' => 'خطة غذائية لمرضى السكري', 'en' => 'Diabetic Diet Plan'],
                    'description' => ['ar' => 'نظام غذائي متوازن للسيطرة على مستويات السكر في الدم.', 'en' => 'Balanced diet plan designed to control blood sugar levels.'],
                    'price' => 600, 'duration' => 60, 'image' => 'services/diabetes-diet.jpg'
                ],
                [
                    'name' => ['ar' => 'برنامج إنقاص وزن مكثف', 'en' => 'Intensive Weight Loss'],
                    'description' => ['ar' => 'برنامج مخصص لخسارة الدهون الصحية بطريقة آمنة ومستدامة.', 'en' => 'Customized program to lose healthy fat safely and sustainably.'],
                    'price' => 450, 'duration' => 45, 'image' => 'services/weight-loss.jpg'
                ],
                [
                    'name' => ['ar' => 'تحليل مكونات الجسم (InBody)', 'en' => 'InBody Analysis'],
                    'description' => ['ar' => 'قياس نسبة الدهون، العضلات، والمياه داخل الجسم بدقة.', 'en' => 'Accurate measurement of body fat, muscle mass, and water.'],
                    'price' => 200, 'duration' => 20, 'image' => 'services/inbody.jpg'
                ],
                [
                    'name' => ['ar' => 'استشارة تغذية رياضيين', 'en' => 'Sports Nutrition Plan'],
                    'description' => ['ar' => 'تغذية مخصصة لرفع الأداء الرياضي وبناء العضلات.', 'en' => 'Tailored nutrition to boost athletic performance and build muscle.'],
                    'price' => 550, 'duration' => 50, 'image' => 'services/sports-nutri.jpg'
                ],
                [
                    'name' => ['ar' => 'علاج النحافة المفرطة', 'en' => 'Underweight Treatment'],
                    'description' => ['ar' => 'برنامج لزيادة الوزن بطريقة صحية وسليمة.', 'en' => 'Program to gain weight in a healthy and proper way.'],
                    'price' => 400, 'duration' => 45, 'image' => 'services/underweight.jpg'
                ],
                [
                    'name' => ['ar' => 'نظام غذائي للحوامل', 'en' => 'Pregnancy Diet Plan'],
                    'description' => ['ar' => 'تغذية صحية متكاملة لصحة الأم والجنين.', 'en' => 'Comprehensive healthy nutrition for mother and baby.'],
                    'price' => 500, 'duration' => 50, 'image' => 'services/pregnancy-diet.jpg'
                ],
            ],
            'dental' => [
                [
                    'name' => ['ar' => 'تنظيف جير وتلميع', 'en' => 'Scaling and Polishing'],
                    'description' => ['ar' => 'إزالة الجير المترسب وحماية اللثة من الالتهابات.', 'en' => 'Removing calculus deposits and protecting gums from inflammation.'],
                    'price' => 700, 'duration' => 45, 'image' => 'services/scaling.jpg'
                ],
                [
                    'name' => ['ar' => 'حشو عصب بالليزر', 'en' => 'Laser Root Canal'],
                    'description' => ['ar' => 'تنظيف وتعقيم جذور الأسنان بأحدث تقنيات الليزر.', 'en' => 'Cleaning and sterilizing root canals using latest laser tech.'],
                    'price' => 1800, 'duration' => 90, 'image' => 'services/root-canal.jpg'
                ],
                [
                    'name' => ['ar' => 'حشو تجميلي', 'en' => 'Cosmetic Filling'],
                    'description' => ['ar' => 'ترميم الأسنان التالفة بمواد لونها مطابق للأسنان الطبيعية.', 'en' => 'Restoring damaged teeth with tooth-colored composite materials.'],
                    'price' => 800, 'duration' => 45, 'image' => 'services/filling.jpg'
                ],
                [
                    'name' => ['ar' => 'تبييض أسنان احترافي', 'en' => 'Professional Teeth Whitening'],
                    'description' => ['ar' => 'تفتيح لون الأسنان لابتسامة أكثر إشراقاً وبياضاً.', 'en' => 'Brightening tooth shade for a more radiant and white smile.'],
                    'price' => 2200, 'duration' => 60, 'image' => 'services/whitening.jpg'
                ],
                [
                    'name' => ['ar' => 'تركيبات زيركون', 'en' => 'Zirconia Crowns'],
                    'description' => ['ar' => 'تلبيسات زيركون قوية ومتينة لحماية الأسنان الضعيفة.', 'en' => 'Strong and durable zirconia crowns to protect weak teeth.'],
                    'price' => 3500, 'duration' => 120, 'image' => 'services/zirconia.jpg'
                ],
                [
                    'name' => ['ar' => 'تقويم أسنان شفاف', 'en' => 'Clear Aligners'],
                    'description' => ['ar' => 'تقويم شفاف غير مرئي لتعديل صفوف الأسنان.', 'en' => 'Invisible clear aligners to straighten teeth discreetly.'],
                    'price' => 12000, 'duration' => 60, 'image' => 'services/aligners.jpg'
                ],
            ],
            'obgyn' => [
                [
                    'name' => ['ar' => 'متابعة حمل دورية', 'en' => 'Regular Pregnancy Checkup'],
                    'description' => ['ar' => 'فحص دوري للاطمئنان على صحة الجنين ونمو الأم.', 'en' => 'Regular checkup to monitor fetal health and mother progression.'],
                    'price' => 500, 'duration' => 30, 'image' => 'services/pregnancy.jpg'
                ],
                [
                    'name' => ['ar' => 'سونار رباعي الأبعاد', 'en' => '4D Ultrasound'],
                    'description' => ['ar' => 'رؤية تفصيلية واضحة لملامح وحركة الجنين.', 'en' => 'Clear detailed view of fetal features and movements.'],
                    'price' => 900, 'duration' => 45, 'image' => 'services/ultrasound.jpg'
                ],
                [
                    'name' => ['ar' => 'علاج تكيس المبايض', 'en' => 'PCOS Treatment'],
                    'description' => ['ar' => 'برنامج علاجي متكامل لتنظيم الهرمونات وعلاج التكيسات.', 'en' => 'Comprehensive treatment program to regulate hormones and PCOS.'],
                    'price' => 600, 'duration' => 45, 'image' => 'services/pcos.jpg'
                ],
                [
                    'name' => ['ar' => 'فحص سرطان عنق الرحم', 'en' => 'Cervical Cancer Screening'],
                    'description' => ['ar' => 'فحص مبكر للاطمئنان والوقاية.', 'en' => 'Early screening for safety and prevention.'],
                    'price' => 1100, 'duration' => 40, 'image' => 'services/cervical-exam.jpg'
                ],
                [
                    'name' => ['ar' => 'علاج تأخر الإنجاب', 'en' => 'Infertility Treatment'],
                    'description' => ['ar' => 'تشخيص أسباب تأخر الحمل ووضع الخطة العلاجية المناسبة.', 'en' => 'Diagnosing causes of delayed pregnancy and planning treatment.'],
                    'price' => 850, 'duration' => 50, 'image' => 'services/infertility.jpg'
                ],
            ],
            'physio' => [
                [
                    'name' => ['ar' => 'جلسة علاج طبيعي مكثفة', 'en' => 'Intensive Physio Session'],
                    'description' => ['ar' => 'جلسة مخصصة لتخفيف الآلام العضلية والمفصلية.', 'en' => 'Session dedicated to relieving muscle and joint pain.'],
                    'price' => 450, 'duration' => 60, 'image' => 'services/physio.jpg'
                ],
                [
                    'name' => ['ar' => 'تأهيل إصابات ملاعب', 'en' => 'Sports Injury Rehab'],
                    'description' => ['ar' => 'برنامج تأهيلي خاص للرياضيين للعودة السريعة للملاعب.', 'en' => 'Special rehab program for athletes to return to sports quickly.'],
                    'price' => 600, 'duration' => 75, 'image' => 'services/sports-rehab.jpg'
                ],
                [
                    'name' => ['ar' => 'علاج الفقرات بالليزر', 'en' => 'Laser Spine Therapy'],
                    'description' => ['ar' => 'علاج آلام الرقبة والظهر والانزلاق الغضروفي بدون جراحة.', 'en' => 'Treating neck, back, and disc pain without surgery.'],
                    'price' => 700, 'duration' => 60, 'image' => 'services/spine-laser.jpg'
                ],
                [
                    'name' => ['ar' => 'تأهيل ما بعد العمليات', 'en' => 'Post-Surgical Rehab'],
                    'description' => ['ar' => 'استعادة الحركة والقوة بعد العمليات الجراحية.', 'en' => 'Restoring mobility and strength after surgical operations.'],
                    'price' => 650, 'duration' => 60, 'image' => 'services/post-surgery.jpg'
                ],
                [
                    'name' => ['ar' => 'حجامة طبية متطورة', 'en' => 'Advanced Cupping'],
                    'description' => ['ar' => 'تحسين الدورة الدموية وتخليص الجسم من السموم.', 'en' => 'Improving blood circulation and detoxifying the body.'],
                    'price' => 400, 'duration' => 45, 'image' => 'services/cupping.jpg'
                ],
            ],
            'psychiatry' => [
                [
                    'name' => ['ar' => 'جلسة إرشاد أسري', 'en' => 'Family Counseling'],
                    'description' => ['ar' => 'جلسة استشارية لحل الخلافات وتعزيز التواصل الأسري.', 'en' => 'Consultation session to resolve conflicts and enhance family communication.'],
                    'price' => 700, 'duration' => 60, 'image' => 'services/family.jpg'
                ],
                [
                    'name' => ['ar' => 'علاج معرفي سلوكي', 'en' => 'Cognitive Behavioral Therapy'],
                    'description' => ['ar' => 'تعديل الأفكار السلبية والسلوكيات المرتبطة بالقلق والتوتر.', 'en' => 'Modifying negative thoughts and behaviors related to anxiety and stress.'],
                    'price' => 800, 'duration' => 50, 'image' => 'services/cbt.jpg'
                ],
                [
                    'name' => ['ar' => 'جلسة دعم نفسي للفرد', 'en' => 'Individual Therapy'],
                    'description' => ['ar' => 'مساحة آمنة للتحدث وتجاوز التحديات النفسية الشخصية.', 'en' => 'A safe space to talk and overcome personal psychological challenges.'],
                    'price' => 600, 'duration' => 50, 'image' => 'services/individual-therapy.jpg'
                ],
                [
                    'name' => ['ar' => 'علاج اضطرابات النوم', 'en' => 'Sleep Disorder Therapy'],
                    'description' => ['ar' => 'تشخيص وعلاج أسباب الأرق واضطرابات النوم المختلفة.', 'en' => 'Diagnosing and treating causes of insomnia and sleep disorders.'],
                    'price' => 750, 'duration' => 50, 'image' => 'services/sleep-disorder.jpg'
                ],
                [
                    'name' => ['ar' => 'تنمية مهارات الأطفال', 'en' => 'Child Development Session'],
                    'description' => ['ar' => 'جلسات لتطوير قدرات الأطفال السلوكية والإدراكية.', 'en' => 'Sessions to develop children behavioral and cognitive abilities.'],
                    'price' => 700, 'duration' => 50, 'image' => 'services/child-dev.jpg'
                ],
            ]
        ];

        $doctors = Doctor::all();
        foreach ($doctors as $doctor) {
            $category = $this->getCategoryBySpecialtyId($doctor->specialty_id);
            
            if (isset($servicesCatalog[$category])) {
                foreach ($servicesCatalog[$category] as $service) {
                    Service::firstOrCreate(
                        [
                            'doctor_id' => $doctor->id,
                            'name->ar'  => $service['name']['ar'],
                        ],
                        [
                            'clinic_id'        => $doctor->clinic_id,
                            'name'             => $service['name'],
                            'description'      => $service['description'],
                            'price'            => $service['price'],
                            'duration_minutes' => $service['duration'],
                            'image'            => $service['image'],
                        ]
                    );
                }
            }
        }
    }

    private function getCategoryBySpecialtyId($id) {
        $map = [
            1 => 'plastic',
            2 => 'dental',
            3 => 'nutrition',
            4 => 'obgyn',
            5 => 'physio',
            6 => 'psychiatry'
        ];
        return $map[$id] ?? 'psychiatry';
    }
}