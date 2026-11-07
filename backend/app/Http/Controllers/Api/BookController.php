<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Book::latest()->get()]);
    }

    public function store(BookRequest $request)
    {
    $book = Book::create($request->validated());

    Log::info('Buku baru disimpan', [
        'aksi'    => 'book.create',
        'user_id' => $request->user()->id,
        'book_id' => $book->id,
        'stok'    => $book->stock,
    ]);

    return response()->json(['message' => 'Buku berhasil ditambahkan', 'data' => $book], 201);
    }

    public function show(Book $book)
    {
        return response()->json(['data' => $book]);
    }

    public function update(BookRequest $request, Book $book)
    {
    $book->update($request->validated());

    Log::info('Data buku diubah', [
        'aksi'    => 'book.update',
        'user_id' => $request->user()->id,
        'book_id' => $book->id,
    ]);

    return response()->json(['message' => 'Buku berhasil diubah', 'data' => $book]);
    }

    public function destroy(Request $request, Book $book)
    {
    $book->delete();

    Log::info('Buku dihapus', [
        'aksi'    => 'book.delete',
        'user_id' => $request->user()->id,
        'book_id' => $book->id,
    ]);

    return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}