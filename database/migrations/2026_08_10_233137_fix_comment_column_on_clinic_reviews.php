<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. محاولة حذف قيد الفحص (Check Constraint) المزعج عن عمود comment إن وجد
        try {
            DB::statement('ALTER TABLE clinic_reviews DROP CHECK clinic_reviews_comment_check');
        } catch (\Exception $e) {
            // تجاهل الخطأ إذا لم يكن الاسم مطابخاً تماماً
        }

        // 2. إعادة تعريف عمود comment ليصبح نص عادي (TEXT) بدون قيود
        Schema::table('clinic_reviews', function (Blueprint $table) {
            $table->text('comment')->change();
        });
    }

    public function down(): void
    {
        //
    }
};