<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'isbn' => $this->isbn,
            'published_date' => optional($this->published_date)->format('Y-m-d'),

            // ⭐ ジャンル情報（仕様書に必須）
            'genres' => $this->genres->map(fn($genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
            ]),

            // ⭐ レビュー一覧（仕様書に必須）
            'reviews' => $this->reviews->map(fn($review) => [
                'user_name' => $review->user->name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
            ]),
        ];
    }
}
