<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. حذف الـ Constraint القديم لو موجود عشان منع التعارض
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check;");

        // 2. إضافة قيد تحقق (Check Constraint) جديد يقبل الأدوار الأربعة
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('patient', 'admin', 'doctor', 'super_admin'));");
    }

    public function down(): void
    {
        // الرجوع للقيد القديم بدون super_admin
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check;");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('patient', 'admin', 'doctor'));");
    }
};