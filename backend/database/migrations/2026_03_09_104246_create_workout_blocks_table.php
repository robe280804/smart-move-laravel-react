<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /*
        Block 1 – Warmup
        Block 2 – Strength
        Block 3 – Accessory
        Block 4 – Core
    */

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workout_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_day_id')->constrained('plan_days')->cascadeOnDelete();

            $table->string('name');  //  Warmup / Strength / Accessory / Sprint / Mobility
            $table->integer('order');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_blocks');
    }
};
