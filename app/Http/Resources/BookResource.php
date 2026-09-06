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
        'published_at' => $this->published_at,

        'genres' => $this->genres?->map(fn($genre) => [
            'id' => $genre->id,
            'name' => $genre->name,
        ]) ?? [],

        'average_rating' => number_format((float) ($this->reviews->avg('rating') ?? 0), 1),
        'review_count' => $this->reviews->count(),
    ];
}

}
