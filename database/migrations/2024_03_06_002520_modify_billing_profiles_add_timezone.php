<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('billing_profiles', function (Blueprint $table) {
            $table->string('timezone');
            $table->double('latitude');
            $table->double('longitude');
            $table->integer('radius');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_profiles', function (Blueprint $table) {
            $table->dropColumn('timezone');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('radius');
        });
    }
};
