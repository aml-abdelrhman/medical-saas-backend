<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // تعديل الـ Enum ليقبل 'super_admin' بجانب الأدوار القديمة
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'admin', 'doctor', 'super_admin') DEFAULT 'patient'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'admin', 'doctor') DEFAULT 'patient'");
    }
};