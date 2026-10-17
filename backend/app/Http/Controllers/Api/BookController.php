<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Models\Book;

class BookController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Book::latest()->get()]);
    }

    public function store(BookRequest $request)
    {
        $book = Book::create($request->validated());

        return response()->json(['message' => 'Buku berhasil ditambahkan', 'data' => $book], 201);
    }

    public function show(Book $book)
    {
        return response()->json(['data' => $book]);
    }

    public function update(BookRequest $request, Book $book)
    {
        $book->update($request->validated());

        return response()->json(['message' => 'Buku berhasil diubah', 'data' => $book]);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}