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
        Schema::create('reading_plans', function (Blueprint $table) {
    $table->id();

    // 紐づくユーザーと書籍
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('book_id')->constrained()->cascadeOnDelete();

    // 読書状態（未読・読書中・完了）
    $table->string('status')->default('not_started')->index();

    // 期限
    $table->date('due_date')->nullable();

    // 完了日時
    $table->timestamp('completed_at')->nullable();

    // 通知済み日時
    $table->timestamp('reminder_sent_at')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_plans');
    }
};
