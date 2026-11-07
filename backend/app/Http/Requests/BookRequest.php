<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'  => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'year'   => ['required', 'integer', 'between:1000,2100'],
            'stock'  => ['required', 'integer', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::warning('Validasi data buku gagal', [
            'aksi'             => $this->isMethod('post') ? 'book.create' : 'book.update',
            'user_id'          => $this->user()?->id,
            'kolom_bermasalah' => array_keys($validator->errors()->toArray()),
            'alasan'           => 'validasi_gagal',
        ]);

        parent::failedValidation($validator);
    }
}