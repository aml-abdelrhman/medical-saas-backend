<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        // تعطيل القيود مؤقتاً لتفريغ الجدول وإعادة ملئه بأمان
        Schema::disableForeignKeyConstraints();
        DB::table('specialties')->truncate();
        Schema::enableForeignKeyConstraints();

        $specialties = [
            [
                'name' => json_encode(['ar' => 'تجميل', 'en' => 'Aesthetics'], JSON_UNESCAPED_UNICODE),
                'slug' => 'aesthetics',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['ar' => 'تغذية', 'en' => 'Nutrition'], JSON_UNESCAPED_UNICODE),
                'slug' => 'nutrition',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['ar' => 'نساء وتوليد', 'en' => 'OB/GYN'], JSON_UNESCAPED_UNICODE),
                'slug' => 'ob-gyn',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['ar' => 'أسنان', 'en' => 'Dentistry'], JSON_UNESCAPED_UNICODE),
                'slug' => 'dentistry',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['ar' => 'علاج طبيعي', 'en' => 'Physical Therapy'], JSON_UNESCAPED_UNICODE),
                'slug' => 'physical-therapy',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['ar' => 'نفسية', 'en' => 'Psychiatry'], JSON_UNESCAPED_UNICODE),
                'slug' => 'psychiatry',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('specialties')->insert($specialties);
    }
}