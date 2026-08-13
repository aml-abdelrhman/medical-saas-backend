<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. تعديل نوع البيانات إلى TEXT مباشرة عبر SQL وبشكل آمن
        DB::statement('ALTER TABLE clinic_reviews ALTER COLUMN comment TYPE TEXT;');

        // 2. إزالة القيمة الافتراضية القديمة إن وجدت لتجنب أي تعارض
        DB::statement('ALTER TABLE clinic_reviews ALTER COLUMN comment DROP DEFAULT;');
    }

    public function down(): void
    {
        // 
    }
};