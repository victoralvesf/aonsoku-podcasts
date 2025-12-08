<?php

namespace App\Http\Requests\Podcast;

use Illuminate\Foundation\Http\FormRequest;

class SearchFilterRequest extends FormRequest
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
            'query' => 'required|min:3',
            'per_page' => 'nullable|integer|min:1|max:50',
            'filter_by' => 'nullable|in:title,description,both',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages()
    {
        return [
            'filter_by.in' => 'The selected filter_by is invalid. Valid values are: title, description, both.',
        ];
    }

    public function queryParameters(): array
    {
        return [
            'query' => [
                'description' => 'The search term.',
                'example' => 'tech',
            ],
            'per_page' => [
                'description' => 'The number of items per page. Min 1, Max 50.',
                'example' => 15,
            ],
            'filter_by' => [
                'description' => 'The field to filter by.',
                'example' => 'title',
            ],
            'page' => [
                'description' => 'The page number.',
                'example' => 1,
            ],
        ];
    }
}
