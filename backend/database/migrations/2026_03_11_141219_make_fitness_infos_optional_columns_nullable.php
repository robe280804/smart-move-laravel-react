<?php

use App\Enums\ExperienceLevel;
use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fitness_infos', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->change();
            $table->enum('gender', array_column(Gender::cases(), 'value'))->nullable()->change();
            $table->enum('experience_level', array_column(ExperienceLevel::cases(), 'value'))->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fitness_infos', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable(false)->change();
            $table->enum('gender', array_column(Gender::cases(), 'value'))->nullable(false)->change();
            $table->enum('experience_level', array_column(ExperienceLevel::cases(), 'value'))->nullable(false)->change();
        });
    }
};
