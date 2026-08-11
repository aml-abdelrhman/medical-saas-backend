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
    Schema::table('doctor_availabilities', function (Blueprint $table) {
        $table->json('day_name')->nullable();
    });
}

public function down()
{
    Schema::table('doctor_availabilities', function (Blueprint $table) {
        $table->dropColumn('day_name');
    });
}
};