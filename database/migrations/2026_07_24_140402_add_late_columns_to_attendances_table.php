<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            $table->enum('status', [
                'On Time',
                'Late',
            ])->default('On Time');

            $table->integer('late_minutes')->default(0);

        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            $table->dropColumn([
                'status',
                'late_minutes'
            ]);

        });
    }
};