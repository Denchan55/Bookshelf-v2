<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReadingPlan extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'target_date',
        'completed_at',
        'reminder_sent_at',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'status' => \App\Enums\ReadingPlanStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
