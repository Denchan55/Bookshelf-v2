<?php

namespace App\Http\Resources;

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

            'genres' => $this->genres->map(fn ($genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
            ]),

            'reviews' => $this->reviews->map(fn ($review) => [
                'user_name' => $review->user->name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
            ]),
            'average_rating' => $this->reviews->avg('rating') !== null
            ? (float) number_format($this->reviews->avg('rating'), 1)
            : null,

            'review_count' => $this->reviews->count(),
        ];
    }
}
