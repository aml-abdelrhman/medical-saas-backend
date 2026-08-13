<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Doctor::truncate();
        Schema::enableForeignKeyConstraints();

        $clinicId = DB::table('clinics')->value('id');
        
        if (!$clinicId) {
            return;
        }

        // جلب التخصصات بربط الـ slug مع الـ id
        $specialties = DB::table('specialties')->pluck('id', 'slug')->toArray();

        // [الحل الذاتي] لو جدول التخصصات فاضي لأي سبب، نقوم بإدخال التخصصات الأساسية فوراً هنا لمنع أي خطأ
        if (empty($specialties)) {
            $defaultSpecialties = [
                ['name' => json_encode(['ar' => 'تجميل', 'en' => 'Aesthetics'], JSON_UNESCAPED_UNICODE), 'slug' => 'aesthetics', 'created_at' => now(), 'updated_at' => now()],
                ['name' => json_encode(['ar' => 'تغذية', 'en' => 'Nutrition'], JSON_UNESCAPED_UNICODE), 'slug' => 'nutrition', 'created_at' => now(), 'updated_at' => now()],
                ['name' => json_encode(['ar' => 'نساء وتوليد', 'en' => 'OB/GYN'], JSON_UNESCAPED_UNICODE), 'slug' => 'ob-gyn', 'created_at' => now(), 'updated_at' => now()],
                ['name' => json_encode(['ar' => 'أسنان', 'en' => 'Dentistry'], JSON_UNESCAPED_UNICODE), 'slug' => 'dentistry', 'created_at' => now(), 'updated_at' => now()],
                ['name' => json_encode(['ar' => 'علاج طبيعي', 'en' => 'Physical Therapy'], JSON_UNESCAPED_UNICODE), 'slug' => 'physical-therapy', 'created_at' => now(), 'updated_at' => now()],
                ['name' => json_encode(['ar' => 'نفسية', 'en' => 'Psychiatry'], JSON_UNESCAPED_UNICODE), 'slug' => 'psychiatry', 'created_at' => now(), 'updated_at' => now()],
            ];
            
            DB::table('specialties')->insert($defaultSpecialties);
            $specialties = DB::table('specialties')->pluck('id', 'slug')->toArray();
        }

        $firstAvailableId = reset($specialties);

        $doctorsData = [
            // تجميل (Aesthetics)
            [
                'name_en' => 'Dr. Nourhan Ali', 
                'name_ar' => 'د. نورهان علي', 
                'specialty' => 'aesthetics', 
                'bio' => [
                    'ar' => "استشاري أول جراحات التجميل والليزر وتنسيق القوام، حاصلة على البورد الأوروبي في الجراحات التجميلية الدقيقة بخبرة تتجاوز 20 عاماً في إجراء عمليات نحت الجسم، شد الوجه والرقبة، وإزالة الدهون بأحدث التقنيات الطبية الآمنة لضمان أعلى معايير الجمال والرضا للمرضى.", 
                    'en' => "Senior Consultant of Plastic Surgery and Laser, with over 20 years of experience in body contouring, facial rejuvenation, and advanced aesthetic procedures."
                ]
            ],
            [
                'name_en' => 'Dr. Sarah Adel', 
                'name_ar' => 'د. سارة عادل', 
                'specialty' => 'aesthetics', 
                'bio' => [
                    'ar' => "أخصائي الأمراض الجلدية والتجميل والليزر العلاجي، متخصصة في علاج مشاكل البشرة المعقدة، إزالة التصبغات، علاج آثار الندبات وحب الشباب باستخدام أحدث تقنيات الليزر الكربوني والبلازما الغنية بالصفائح الدموية (PRP).", 
                    'en' => "Dermatology and Aesthetics Specialist, focusing on advanced skin treatments, scar revision, and laser skin resurfacing techniques."
                ]
            ],
            [
                'name_en' => 'Dr. Mahmoud Kamal', 
                'name_ar' => 'د. محمود كمال', 
                'specialty' => 'aesthetics', 
                'bio' => [
                    'ar' => "خبير التجميل والعلاج غير الجراحي، متخصص ببرتوكولات حقن الفيلر التجميلي، البوتكس العلاجي والوقائي، خيوط الشد التجميلية، وتجديد نضارة الوجه واليدين بأحدث المواد العالمية المصرح بها.", 
                    'en' => "Cosmetic expert specializing in non-surgical facial enhancements, advanced filler and botox injection techniques, and anti-aging treatments."
                ]
            ],
            [
                'name_en' => 'Dr. Reem Khaled', 
                'name_ar' => 'د. ريم خالد', 
                'specialty' => 'aesthetics', 
                'bio' => [
                    'ar' => "أخصائي تجميل ومكافحة الشيخوخة، مهتمة بتقديم برامج العناية الوقائية للبشرة، التحفيز الكولاجيني، وعلاجات الميزوثيرابي للشعر والبشرة لتحقيق إطلالة طبيعية ومشرقة تدوم طويلاً.", 
                    'en' => "Aesthetics and anti-aging specialist focusing on preventive skin care programs, collagen stimulation, and mesotherapy treatments."
                ]
            ],

            // تغذية (Nutrition)
            [
                'name_en' => 'Dr. Heba Mahmoud', 
                'name_ar' => 'د. هبة محمود', 
                'specialty' => 'nutrition', 
                'bio' => [
                    'ar' => "أخصائي تغذية علاجية معتمدة من الكلية الأمريكية للتغذية، متخصصة في وضع خطط غذائية مخصصة لإنقاص الوزن، التعامل مع حالات السمنة المفرطة، وتنظيم مستويات السكر والضغط والكوليسترول عبر التغذية السليمة.", 
                    'en' => "Certified Clinical Nutritionist specializing in weight management, personalized dietary planning, and medical nutrition therapy for chronic conditions."
                ]
            ],
            [
                'name_en' => 'Dr. Mona Salem', 
                'name_ar' => 'د. منى سالم', 
                'specialty' => 'nutrition', 
                'bio' => [
                    'ar' => "خبير تغذية الرياضيين والأنظمة الغذائية الخاصة، تهتم بتطوير البرامج الغذائية للرفع من كفاءة الأداء البدني، بناء الكتلة العضلية، وتصميم أنظمة نباتية متكاملة العناصر.", 
                    'en' => "Clinical Nutritionist focused on sports nutrition, athletic performance enhancement, muscle mass development, and specialized plant-based diets."
                ]
            ],
            [
                'name_en' => 'Dr. Tarek Youssef', 
                'name_ar' => 'د. طارق يوسف', 
                'specialty' => 'nutrition', 
                'bio' => [
                    'ar' => "استشاري السمنة والنحافة وجراحات الجهاز الهضمي المرتبطة بالوزن، خبير في المتابعة الطبية قبل وبعد جراحات السمنة المفرطة لضمان ثبات الوزن والصحة العامة.", 
                    'en' => "Obesity and weight loss consultant, specializing in bariatric medical follow-ups and metabolic nutritional rehabilitation programs."
                ]
            ],
            [
                'name_en' => 'Dr. Nadia Hassan', 
                'name_ar' => 'د. نادية حسن', 
                'specialty' => 'nutrition', 
                'bio' => [
                    'ar' => "مستشار تغذية متخصصة في صحة الأطفال، النمو السليم، علاج سوء التغذية، والتعامل مع الحساسيات الغذائية المختلفة للأطفال والمراهقين لضمان نمو صحي ومتوازن.", 
                    'en' => "Nutrition consultant specializing in pediatric health, proper growth, nutritional deficiencies management, and food allergies in children."
                ]
            ],

            // نساء وتوليد (OB/GYN)
            [
                'name_en' => 'Dr. Mona Adel', 
                'name_ar' => 'د. منى عادل', 
                'specialty' => 'ob-gyn', 
                'bio' => [
                    'ar' => "استشاري النساء والتوليد ومتابعة الحمل عالي الخطورة، خبرة طويلة في التعامل مع حالات الحمل المعقدة، الولادة الطبيعية والقيصرية الآمنة، واستخدام أحدث أجهزة السونار الرباعي.", 
                    'en' => "OB/GYN consultant with extensive experience in high-risk pregnancy monitoring, safe deliveries, and advanced obstetric ultrasound diagnostics."
                ]
            ],
            [
                'name_en' => 'Dr. Laila Youssef', 
                'name_ar' => 'د. ليلى يوسف', 
                'specialty' => 'ob-gyn', 
                'bio' => [
                    'ar' => "أخصائي توليد ومتابعة حمل، تقدم دعماً طبياً ونفسياً متكاملاً للمرأة خلال مراحل الحمل المختلفة، ومتابعة الصحة الإنجابية العامة وتوعية الأمهات الجدد.", 
                    'en' => "Obstetrician and pregnancy monitoring specialist providing comprehensive medical and psychological support for women throughout pregnancy."
                ]
            ],
            [
                'name_en' => 'Dr. Noha Ibrahim', 
                'name_ar' => 'د. نهى إبراهيم', 
                'specialty' => 'ob-gyn', 
                'bio' => [
                    'ar' => "استشاري علاج العقم والمساعدة على الإنجاب وتقنيات الحقن المجهري وأطفال الأنابيب، حاصلة على زمالات دولية متقدمة في علاج تأخر الإنجاب واضطرابات الهرمونات النسائية.", 
                    'en' => "Infertility and assisted reproductive technology consultant, expert in ICSI, IVF procedures, and hormonal disorder treatments."
                ]
            ],
            [
                'name_en' => 'Dr. Samar Ali', 
                'name_ar' => 'د. سمر علي', 
                'specialty' => 'ob-gyn', 
                'bio' => [
                    'ar' => "أخصائي النساء والتوليد، متخصصة في جراحات المناظير النسائية، علاج تكيسات المبايض، اضطرابات الدورة الشهرية، وتقديم الرعاية الوقائية والدورية لصحة المرأة.", 
                    'en' => "Gynecologist specializing in gynecological laparoscopic surgeries, ovarian cysts treatment, and menstrual disorders management."
                ]
            ],

            // أسنان (Dentistry)
            [
                'name_en' => 'Dr. Ahmed Mahmoud', 
                'name_ar' => 'د. أحمد محمود', 
                'specialty' => 'dentistry', 
                'bio' => [
                    'ar' => "طبيب أسنان عام وتجميلي، متخصص في التركيبات الثابتة والمتحركة، تجميل الأسنان الأمامية، وعلاج جذور الأسنان بأحدث التقنيات الرقمية لضمان علاج بدون ألم.", 
                    'en' => "General and cosmetic dentist specializing in fixed and removable prosthodontics, aesthetic restorations, and painless root canal treatments."
                ]
            ],
            [
                'name_en' => 'Dr. Mohamed Tarek', 
                'name_ar' => 'د. محمد طارق', 
                'specialty' => 'dentistry', 
                'bio' => [
                    'ar' => "أخصائي تجميل الأسنان وتقويم الأسنان الرقمي باستخدام أحدث أنظمة التصميم الثلاثي الأبعاد، خبير في تصميم الابتسامة (Hollywood Smile) وتركيب العدسات التجميلية (Veneers).", 
                    'en' => "Cosmetic dentist and digital orthodontist using advanced 3D planning systems, specialized in Hollywood Smile and Veneers."
                ]
            ],
            [
                'name_en' => 'Dr. Alaa Saeed', 
                'name_ar' => 'د. علاء سعيد', 
                'specialty' => 'dentistry', 
                'bio' => [
                    'ar' => "استشاري جراحة الفم والأسنان، خبير في زراعة الأسنان الفورية، جراحات عظام الفكين المعقدة، وخلع الضرس الجراحي المعقد بأمان تام وتقنيات تخدير حديثة.", 
                    'en' => "Oral and maxillofacial surgeon, expert in dental implants, complex jaw surgeries, and advanced surgical extractions."
                ]
            ],
            [
                'name_en' => 'Dr. Mayada Fathy', 
                'name_ar' => 'د. ميادة فتحي', 
                'specialty' => 'dentistry', 
                'bio' => [
                    'ar' => "أخصائي تقويم الأسنان للبالغين والأطفال، متخصصة في تقويم الأسنان المعدني والشفاف وتقنيات تصحيح إطباق الأسنان لتحسين الوظيفة والمظهر الجمالي.", 
                    'en' => "Orthodontist for adults and children, specialized in clear aligners, traditional braces, and bite correction techniques."
                ]
            ],

            // علاج طبيعي (Physical Therapy)
            [
                'name_en' => 'Dr. Karim Hassan', 
                'name_ar' => 'د. كريم حسن', 
                'specialty' => 'physical-therapy', 
                'bio' => [
                    'ar' => "أخصائي العلاج الطبيعي والتأهيل الحركي، خبير في علاج آلام الظهر المزمنة، الانزلاق الغضروفي بدون جراحة، وإعادة التأهيل العصبي والعضلي للمفاصل باستخدام أحدث أجهزة العلاج الكهربائي واليدوي.", 
                    'en' => "Physiotherapist and movement rehabilitation specialist for chronic back pain, non-surgical disc treatments, and joint rehabilitation."
                ]
            ],
            [
                'name_en' => 'Dr. Omar Farouk', 
                'name_ar' => 'د. عمر فاروق', 
                'specialty' => 'physical-therapy', 
                'bio' => [
                    'ar' => "خبير إصابات الملاعب والتأهيل الرياضي المتقدم، متخصص في إعادة تأهيل الرياضيين بعد جراحات الرباط الصليبي وإصابات الملاعب للعودة السريعة والآمنة للملاعب.", 
                    'en' => "Sports injury expert and athletic rehabilitation specialist focusing on post-ACL surgery recovery and athletic performance restoration."
                ]
            ],
            [
                'name_en' => 'Dr. Yasmin Kamal', 
                'name_ar' => 'د. ياسمين كمال', 
                'specialty' => 'physical-therapy', 
                'bio' => [
                    'ar' => "أخصائي التأهيل الحركي والعصبي، متخصصة في تأهيل حالات إصابات الجهاز العصبي المركزي، السكتات الدماغية، ومساعدة المرضى على استعادة الاستقلالية الحركية.", 
                    'en' => "Movement and neurological rehabilitation specialist for stroke recovery and central nervous system injuries."
                ]
            ],
            [
                'name_en' => 'Dr. Hany Mamdouh', 
                'name_ar' => 'د. هاني ممدوح', 
                'specialty' => 'physical-therapy', 
                'bio' => [
                    'ar' => "أخصائي تأهيل العمود الفقري وتصحيح القوام، خبير في علاج تشوهات القوام عند الأطفال والكبار، وعلاج الآلام الناتجة عن ضغوط العمل المكتبية الطويلة.", 
                    'en' => "Spine specialist and posture rehabilitation expert, focusing on posture correction and work-related musculoskeletal pain."
                ]
            ],

            // نفسية (Psychiatry)
            [
                'name_en' => 'Dr. Mostafa Kamal', 
                'name_ar' => 'د. مصطفى كمال', 
                'specialty' => 'psychiatry', 
                'bio' => [
                    'ar' => "استشاري الطب النفسي وعلاج الإدمان، خبير في تشخيص وعلاج اضطرابات القلق، الاكتئاب الحاد، نوبات الهلع، والاضطرابات المزاجية عبر برامج علاجية متكاملة وسرية تامة.", 
                    'en' => "Psychiatrist and mental health consultant expert in treating anxiety disorders, severe depression, panic attacks, and mood disorders."
                ]
            ],
            [
                'name_en' => 'Dr. Khaled Ibrahim', 
                'name_ar' => 'د. خالد إبراهيم', 
                'specialty' => 'psychiatry', 
                'bio' => [
                    'ar' => "معالج نفسي سلوكي ومعرفي (CBT)، متخصص في تعديل السلوك، علاج الوسواس القهري (OCD)، اضطرابات الخوف الاجتماعي، وتقديم الاستشارات الإرشادية الفردية.", 
                    'en' => "Cognitive Behavioral Therapist (CBT) specializing in behavior modification, OCD treatment, and social anxiety therapy."
                ]
            ],
            [
                'name_en' => 'Dr. Iman Abdullah', 
                'name_ar' => 'د. إيمان عبد الله', 
                'specialty' => 'psychiatry', 
                'bio' => [
                    'ar' => "أخصائي علم النفس الإكلينيكي، تركز على الصحة النفسية للمرأة، الدعم النفسي خلال فترة الحمل وما بعد الولادة، واضطرابات صعوبات التعلم عند الأطفال.", 
                    'en' => "Clinical psychologist focusing on women's mental health, postpartum psychological support, and children's learning difficulties."
                ]
            ],
            [
                'name_en' => 'Dr. Youssef Rashad', 
                'name_ar' => 'د. يوسف رشاد', 
                'specialty' => 'psychiatry', 
                'bio' => [
                    'ar' => "مستشار أسري ونفسي، متخصص في حل النزاعات الزوجية والأسرية، الإرشاد الأسري، وتطوير مهارات التواصل الفعال والتعامل ضغوط الحياة اليومية.", 
                    'en' => "Family and psychological consultant expert in resolving marital conflicts, family counseling, and stress management."
                ]
            ],
        ];

        foreach ($doctorsData as $index => $doc) {
            $specialtyId = $specialties[$doc['specialty']] ?? $firstAvailableId;
            $imageNumber = $index + 1;

            $email = 'doctor' . ($index + 1) . '@clinic.com';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $doc['name_ar'],
                    'password' => Hash::make('password'),
                    'role' => 'doctor',
                    'clinic_id' => $clinicId,
                ]
            );

            Doctor::create([
                'name' => ['ar' => $doc['name_ar'], 'en' => $doc['name_en']],
                'slug' => Str::slug($doc['name_en']),
                'clinic_id' => $clinicId,
                'user_id' => $user->id,
                'specialty_id' => $specialtyId,
                'bio' => $doc['bio'],
                'years_experience' => rand(8, 22),
                'rating' => rand(47, 50) / 10,
                'price_from' => rand(350, 900),
                'languages' => ['ar', 'en'],
                'image' => "doctors/doctor{$imageNumber}.jpg",
            ]);
        }
    }
}