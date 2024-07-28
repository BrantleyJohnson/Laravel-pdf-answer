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
        Schema::create('master_user_chats', function (Blueprint $table) {
            $table->id();
            $table->string('chatgpt_id', 1024)->nullable();
            $table->string('name', 2024);
            $table->string('sharable_link', 2024);
            $table->string('share_name', 5)->default('no');
            $table->string('is_archive', 5)->default('no');
            $table->foreignId('user_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_user_chats');
    }
};
