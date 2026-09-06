<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_at',
        'description',
        'image_url',
        'user_id',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    // ⭐ 追加：ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ⭐ 修正：中間テーブル名を明示
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    // ⭐ 追加：Favorite モデルとのリレーション
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // 既存：ユーザーがお気に入りにした一覧
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')
                    ->withTimestamps();
    }

    // 既存：レビュー
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
