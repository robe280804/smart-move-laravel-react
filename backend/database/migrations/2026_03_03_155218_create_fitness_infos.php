<?php

use App\Enums\ExperienceLevel;
use App\Enums\Gender;
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
        Schema::create('fitness_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->decimal('height', 5, 2)->comment('Height in centimeters');
            $table->decimal('weight', 5, 2)->comment('Weight in kilograms');
            $table->unsignedTinyInteger('age');
            $table->enum('gender', array_column(Gender::cases(), 'value'));
            $table->enum('experience_level', array_column(ExperienceLevel::cases(), 'value'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fitness_infos');
    }
};
