<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tanpa_token_ditolak_401(): void
    {
        $this->getJson('/api/books')->assertStatus(401);
    }

    public function test_tambah_buku_mengembalikan_201(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', [
            'title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata', 'year' => 2005, 'stock' => 3,
        ])->assertStatus(201)->assertJsonPath('data.title', 'Laskar Pelangi');

        $this->assertDatabaseHas('books', ['title' => 'Laskar Pelangi']);
    }

    public function test_validasi_gagal_mengembalikan_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', ['title' => ''])->assertStatus(422);
    }

    public function test_hapus_buku(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $book = Book::factory()->create();

        $this->deleteJson("/api/books/{$book->id}")->assertOk();

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}