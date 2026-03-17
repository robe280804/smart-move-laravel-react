<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY stripe_id VARCHAR(255) COLLATE utf8_bin NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY stripe_id VARCHAR(255) NULL');
    }
};
