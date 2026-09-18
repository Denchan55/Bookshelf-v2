<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertInstanceOf(User::class, $favorite->user);
        $this->assertEquals($user->id, $favorite->user->id);
    }

    #[Test]
    public function it_belongs_to_a_book()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertInstanceOf(Book::class, $favorite->book);
        $this->assertEquals($book->id, $favorite->book->id);
    }
}
