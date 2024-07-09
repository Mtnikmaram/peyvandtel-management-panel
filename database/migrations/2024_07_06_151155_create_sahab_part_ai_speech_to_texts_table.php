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
        Schema::create('sahab_part_ai_speech_to_texts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['waiting', 'processing', 'failed', 'successful'])->default('waiting');
            $table->integer("used_credit")->unsigned();
            $table->string('file');
            $table->smallInteger('file_length')->unsigned();
            $table->json("result")->nullable();
            $table->json("payload")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sahab_part_ai_speech_to_texts');
    }
};
