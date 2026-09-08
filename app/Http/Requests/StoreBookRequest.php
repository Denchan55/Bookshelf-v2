<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
    return [
        'title' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image_url' => 'nullable|url',
        'genres' => 'required|array',
        'genres.*' => 'exists:genres,id',
        'isbn' => 'required|string|size:13|unique:books,isbn',
        'published_date' => 'required|date',

    ];
}

public function messages()
{
    return [
        'title.required' => 'タイトルは必須です。',
        'title.string' => 'タイトルは文字列で入力してください。',
        'title.max' => 'タイトルは255文字以内で入力してください。',
        'author.required' => '著者名は必須です。',
        'author.string' => '著者名は文字列で入力してください。',
        'author.max' => '著者名は255文字以内で入力してください。',
        'image_url.url' => '画像URLは有効なURL形式で入力してください。',
        'image_url.string' => '画像URLは文字列で入力してください。',
        'genres.required' => 'ジャンルは必須です。',
        'genres.array' => 'ジャンルは配列で入力してください。',
        'genres.*.exists' => '選択されたジャンルは存在しません。',
        'isbn.required' => 'ISBNは必須です。',
        'isbn.size' => 'ISBNは13桁で入力してください。',
        'isbn.unique' => 'このISBNは既に使用されています。',
        'published_date.required' => '出版日は必須です。',
        'published_date.date' => '出版日は有効な日付形式で入力してください。',
        'description.string' => '説明は文字列で入力してください。',
        ];
}

}
