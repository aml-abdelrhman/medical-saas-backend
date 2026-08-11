<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AvailabilitySeeder extends Seeder
{
    public function run()
    {
        // مسح الجدول أو تعطيل القيود مؤقتاً للتنظيف إذا لزم الأمر
        Schema::disableForeignKeyConstraints();
        DB::table('doctor_availabilities')->truncate(); // تأكد من اسم الجدول في قاعدة البيانات (قد يكون doctor_availabilities أو doctor_availability)
        Schema::enableForeignKeyConstraints();

        // مصفوفة الترجمات وأوقات العمل
        $daysData = [
            0 => ['ar' => 'الأحد', 'en' => 'Sunday'],
            1 => ['ar' => 'الاثنين', 'en' => 'Monday'],
            2 => ['ar' => 'الثلاثاء', 'en' => 'Tuesday'],
            3 => ['ar' => 'الأربعاء', 'en' => 'Wednesday'],
            4 => ['ar' => 'الخميس', 'en' => 'Thursday'],
            5 => ['ar' => 'الجمعة', 'en' => 'Friday'],
            6 => ['ar' => 'السبت', 'en' => 'Saturday'],
        ];

        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            // أيام العمل المعتادة من الأحد إلى الخميس
            $workingDays = [0, 1, 2, 3, 4];

            foreach ($workingDays as $dayIndex) {
                DoctorAvailability::updateOrCreate(
                    [
                        'doctor_id'   => $doctor->id,
                        'day_of_week' => $dayIndex,
                    ],
                    [
                        'day_name'    => $daysData[$dayIndex], // سيتم تخزينه كـ JSON
                        'start_time'  => '09:00:00',
                        'end_time'    => '17:00:00',
                        'is_active'   => true,
                    ]
                );
            }
        }
    }
}