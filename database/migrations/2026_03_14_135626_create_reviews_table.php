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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->integer('rating')->nullable(); // 1-5
            $table->string('author')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('telegram_message_id')->nullable()->unique();
            $table->string('telegram_chat_id')->nullable();
            $table->string('telegram_author_id')->nullable();
            $table->string('telegram_author_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
