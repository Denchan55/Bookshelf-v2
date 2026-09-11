<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NOT_STARTED => '未読',
            self::IN_PROGRESS => '進行中',
            self::Completed => '完了',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'bg-gray-200 text-gray-800',
            self::IN_PROGRESS => 'bg-blue-200 text-blue-800',
            self::Completed => 'bg-green-200 text-green-800',
        };
    }
}
