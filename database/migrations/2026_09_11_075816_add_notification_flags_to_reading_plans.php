<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reading_plans', function (Blueprint $table) {
            // $table->timestamp('reminder_sent_at')->nullable();            // 期限3日前通知
            $table->timestamp('deadline_today_sent_at')->nullable();      // 期限当日通知
            $table->timestamp('deadline_passed_sent_at')->nullable();     // 期限超過通知
            $table->timestamp('no_progress_sent_at')->nullable();         // 放置通知
        });
    }

    public function down(): void
    {
        Schema::table('reading_plans', function (Blueprint $table) {
            $table->dropColumn([
                // 'reminder_sent_at',
                'deadline_today_sent_at',
                'deadline_passed_sent_at',
                'no_progress_sent_at',
            ]);
        });
    }
};
