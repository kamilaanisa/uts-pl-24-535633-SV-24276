<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
public function authorize(): bool { return true; }

public function rules(): array
{
    return [
        'title'  => ['required', 'string', 'max:255'],
        'author' => ['required', 'string', 'max:255'],
        'year'   => ['required', 'integer', 'between:1000,2100'],
        'stock'  => ['required', 'integer', 'min:0'],
    ];
}
}
