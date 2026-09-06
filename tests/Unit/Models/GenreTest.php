<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Genre;
use App\Models\Book;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_many_books()
    {
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(3)->create();

        // 中間テーブルに紐付け
        $genre->books()->sync($books->pluck('id')->toArray());

        // リレーションの確認
        $this->assertCount(3, $genre->books);
        $this->assertInstanceOf(Book::class, $genre->books->first());
    }
}
