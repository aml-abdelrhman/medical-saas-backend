<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run()
    {
        // مسح الجدول أو تعطيل القيود مؤقتاً للتنظيف
        Schema::disableForeignKeyConstraints();
        DB::table('appointments')->truncate();
        Schema::enableForeignKeyConstraints();

        // نجلب المرضى، والأطباء
        $patients = User::where('role', 'patient')->get();
        $doctors = Doctor::all();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            return; // تأكد من وجود بيانات في الجداول الأساسية أولاً
        }

        foreach ($doctors as $doctor) {
            $services = Service::where('doctor_id', $doctor->id)->get();
            
            // إذا لم تكن هناك خدمات مرتبطة بهذا الطبيب، نتخطاه لتجنب الأخطاء
            if ($services->isEmpty()) {
                continue;
            }

            // لكل دكتور ننشئ 4 مواعيد مختلفة تماماً
            for ($i = 0; $i < 4; $i++) {
                $patient = $patients->random();
                $service = $services->random();
                
                // نختار تاريخاً عشوائياً مختلفاً في الأيام القادمة
                $date = Carbon::now()->addDays(rand(1, 10));
                
                // أوقات مختلفة لتجنب التضارب (توزيع الساعات من 9 صباحاً إلى 4 عصراً)
                $hour = 9 + ($i * 2); 
                $startTime = Carbon::createFromTime($hour, [0, 30][rand(0, 1)]);
                $endTime = $startTime->copy()->addMinutes($service->duration_minutes ?? 30);

                Appointment::create([
                    'clinic_id'        => $doctor->clinic_id,
                    'patient_id'       => $patient->id,
                    'doctor_id'        => $doctor->id,
                    'service_id'       => $service->id,
                    'appointment_date' => $date->format('Y-m-d'),
                    'start_time'       => $startTime->format('H:i'),
                    'end_time'         => $endTime->format('H:i'),
                    'status'           => ['confirmed', 'cancelled', 'completed'][rand(0, 2)],
                    'notes'            => [
                        'ar' => 'ملاحظة تجريبية للموعد رقم ' . ($i + 1),
                        'en' => 'Test note for appointment number ' . ($i + 1)
                    ],
                ]);
            }
        }
    }
}