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
        Schema::create('billing_profile_vehicle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('billing_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function(Blueprint $table) {
            $table->foreignId('billing_profile_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::dropIfExists('billing_profile_vehicle');
    }
};
