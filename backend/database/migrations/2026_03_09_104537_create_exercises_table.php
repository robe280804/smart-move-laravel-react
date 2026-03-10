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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();

            $table->string('category');    // strength | sprint | mobility | conditioning
            $table->string('muscle_group')->nullable();
            $table->string('equipment')->nullable();
            $table->text('instructions')->nullable();
            $table->text('infos')->nullable();
            $table->json('additional_metrics')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
