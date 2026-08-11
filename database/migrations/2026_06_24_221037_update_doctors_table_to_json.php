<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('doctors', function (Blueprint $table) {
        // تحويل الأعمدة إلى json (نستخدم change لعدم مسح البيانات الموجودة إذا كانت متوافقة)
        $table->json('name')->change();
        $table->json('bio')->nullable()->change();
    });
}

public function down()
{
    Schema::table('doctors', function (Blueprint $table) {
        $table->string('name')->change();
        $table->text('bio')->nullable()->change();
    });
}
};
