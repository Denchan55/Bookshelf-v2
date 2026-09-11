<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReadingPlanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
{
    return [
        'book_id' => [
            'required',
            Rule::unique('reading_plans')->where(fn ($q) =>
                $q->where('user_id', auth()->id())
            ),
        ],
        'target_date' => ['required', 'date'],
    ];
}

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '選択した書籍が存在しません。',
            'target_date.required' => '期日を入力してください。',
            'target_date.date' => '期日は正しい日付形式で入力してください。',
            'book_id.unique' => 'この書籍は既に登録されています。',
        ];
    }
}

