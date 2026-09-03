<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
protected $redirect = null;
    protected $redirectRoute = null;
    protected $redirectUrl = null;

    public function wantsJson()
    {
        return true;
    }
    public function rules()
    {

        return [
            // キーワード検索
            'keyword' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
