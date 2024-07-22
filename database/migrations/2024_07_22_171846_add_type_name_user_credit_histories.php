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
        Schema::table('user_credit_histories', function (Blueprint $table) {
            $table->string('type_name', 50)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_credit_histories', function (Blueprint $table) {
            $table->dropColumn('type_name');
        });
    }
};
