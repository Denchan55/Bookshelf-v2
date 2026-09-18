<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_many_books()
    {
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(3)->create();

        $genre->books()->sync($books->pluck('id')->toArray());

        $this->assertCount(3, $genre->books);
        $this->assertInstanceOf(Book::class, $genre->books->first());
    }
}
