<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_at',
        'description',
        'image_url',
        'user_id',
    ];

    public function genres()
{
    return $this->belongsToMany(Genre::class);

}
    public function favoritedByUsers()
{
    return $this->belongsToMany(User::class, 'favorites')
                ->withTimestamps();
}

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    use HasFactory;

}
