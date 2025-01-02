<?php

namespace App\Http\Requests\Episode;

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
            'per_page' => 'nullable|integer|min:10|max:100',
            'order_by' => 'nullable|in:published_at,title,duration',
            'sort' => 'nullable|in:asc,desc',
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
            'order_by.in' => 'The selected order_by is invalid. Valid values are: published_at, title, duration.',
            'sort.in' => 'The selected sort is invalid. Valid values are: asc, desc.',
            'filter_by.in' => 'The selected filter_by is invalid. Valid values are: title, description, both.',
        ];
    }
}
