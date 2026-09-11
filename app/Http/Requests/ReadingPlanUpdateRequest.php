<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReadingPlanUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_date.required' => '期日を入力してください。',
            'target_date.date' => '期日は正しい日付形式で入力してください。',
        ];
    }
}

