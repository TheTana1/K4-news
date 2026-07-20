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
        Schema::table('advertisements', function (Blueprint $table) {
            if (Schema::hasColumn('advertisements', 'telegram_author_id')) {
                $table->dropColumn('telegram_author_id');
            }

            if (Schema::hasColumn('advertisements', 'telegram_chat_id')) {
                $table->dropColumn('telegram_chat_id');
            }

            // Добавляем статус, если его нет
            if (!Schema::hasColumn('advertisements', 'status')) {
                $table->boolean('status')->default(true)->after('content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            if (!Schema::hasColumn('advertisements', 'telegram_author_id')) {
                $table->integer('telegram_author_id')->nullable();
            }

            if (!Schema::hasColumn('advertisements', 'telegram_chat_id')) {
                $table->integer('telegram_chat_id')->nullable();
            }

            if (Schema::hasColumn('advertisements', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
