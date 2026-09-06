<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\User;
use App\Models\Genre;
use App\Models\Review;
use App\Models\Favorite;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $book->user);
        $this->assertEquals($user->id, $book->user->id);
    }

    /** @test */
    public function it_has_many_reviews()
    {
        $book = Book::factory()->create();
        Review::factory()->count(3)->create(['book_id' => $book->id]);

        $this->assertCount(3, $book->reviews);
        $this->assertInstanceOf(Review::class, $book->reviews->first());
    }

    /** @test */
    public function it_has_many_genres()
    {
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $book->genres()->sync($genres->pluck('id')->toArray());

        $this->assertCount(2, $book->genres);
        $this->assertInstanceOf(Genre::class, $book->genres->first());
    }

    /** @test */
    public function it_has_many_favorites()
    {
        $book = Book::factory()->create();
        Favorite::factory()->count(2)->create(['book_id' => $book->id]);

        $this->assertCount(2, $book->favorites);
        $this->assertInstanceOf(Favorite::class, $book->favorites->first());
    }

    /** @test */
    public function it_can_store_basic_attributes()
    {
        $book = Book::factory()->create([
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9781234567890',
            'description' => '説明文',
            'image_url' => 'https://example.com/img.jpg',
            'published_at' => '2024-01-01',
        ]);

        $this->assertEquals('吾輩は猫である', $book->title);
        $this->assertEquals('夏目漱石', $book->author);
        $this->assertEquals('9781234567890', $book->isbn);
        $this->assertEquals('説明文', $book->description);
        $this->assertEquals('https://example.com/img.jpg', $book->image_url);
        $this->assertEquals('2024-01-01', $book->published_at->format('Y-m-d'));
    }
}
