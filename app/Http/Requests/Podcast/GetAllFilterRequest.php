<?php

namespace App\Http\Requests\Podcast;

use Illuminate\Foundation\Http\FormRequest;

class GetAllFilterRequest extends FormRequest
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
            'per_page' => 'nullable|integer|min:10|max:100',
            'order_by' => 'nullable|in:title,episode_count',
            'sort' => 'nullable|in:asc,desc',
        ];
    }

    public function queryParameters(): array
    {
        return [
            'per_page' => [
                'description' => 'The number of items per page. Min 10, Max 100.',
                'example' => 15,
            ],
            'order_by' => [
                'description' => 'The field to order by.',
                'example' => 'title',
            ],
            'sort' => [
                'description' => 'The sorting direction.',
                'example' => 'asc',
            ],
            'page' => [
                'description' => 'The page number.',
                'example' => 1,
            ],
        ];
    }
}
