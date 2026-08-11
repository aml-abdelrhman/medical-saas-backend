<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_availabilities', function (Blueprint $table) {
            $table->id();
            // ربط الجدول بجدول الأطباء
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            
            // تحديد يوم الأسبوع: 0 للأحد، 1 للإثنين ... 6 للسبت
            $table->unsignedTinyInteger('day_of_week'); 
            
            // وقت بداية ونهاية الدوام في هذا اليوم
            $table->time('start_time'); 
            $table->time('end_time');   
            
            // حالة اليوم (متاح للعمل أم مغلق مؤقتاً)
            $table->boolean('is_active')->default(true); 
            
            $table->timestamps();
            
            // منع تكرار نفس اليوم لنفس الطبيب لضمان نظافة البيانات
            $table->unique(['doctor_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_availabilities');
    }
};