<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // تحويل العمود إلى json لدعم اللغتين
            $table->json('notes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // التراجع إلى text
            $table->text('notes')->nullable()->change();
        });
    }
};