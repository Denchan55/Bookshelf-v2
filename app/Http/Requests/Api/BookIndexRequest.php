<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BookIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // キーワード検索
            'keyword' => ['nullable', 'string', 'max:255'],

            // ページ番号（ページネーション）
            'page' => ['nullable', 'integer', 'min:1'],

            // 1ページあたりの件数（負荷対策）
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
