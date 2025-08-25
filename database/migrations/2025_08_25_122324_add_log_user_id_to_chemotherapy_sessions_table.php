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
        Schema::table('chemotherapy_sessions', function (Blueprint $table) {
            $table->foreignId('log_user_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chemotherapy_sessions', function (Blueprint $table) {
            $table->dropForeign(['log_user_id']);
            $table->dropColumn('log_user_id');
        });
    }
};
