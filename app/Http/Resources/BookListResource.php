<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => optional($this->published_date)->format('Y-m-d'),
            'image_url' => $this->image_url,

            'average_rating' => (float) $this->reviews_avg_rating,
            'review_count' => $this->reviews_count,
            'genres' => $this->genres->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
            ]),
        ];
    }
}
