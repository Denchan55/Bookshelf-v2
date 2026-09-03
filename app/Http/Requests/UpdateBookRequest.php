<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
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
        'isbn' => 'required|string|size:13|unique:books,isbn,' . $this->book->id,
        'published_at' => 'required|date',
    ];
}
public function messages()
{
    return [
        'title.required' => 'タイトルを入力してください。',
        'author.required' => '著者名を入力してください。',
        'image_url.url' => '画像URLは有効なURL形式で入力してください。',
        'genres.required' => 'ジャンルを選択してください。',
        'genres.*.exists' => '選択されたジャンルは存在しません。',
        'isbn.required' => 'ISBNを入力してください。',
        'isbn.size' => 'ISBNは13桁で入力してください。',
        'isbn.unique' => 'このISBNは既に登録されています。',
        'published_at.required' => '出版日を入力してください。',
        'published_at.date' => '出版日は有効な日付形式で入力してください。',
    ];
}

}
