<?php

namespace App\Http\Requests\Podcast;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePodcastRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'feed_url'    => ['nullable', 'string', 'url', 'required_without:feed_urls', 'prohibits:feed_urls'],
            'feed_urls'   => ['nullable', 'array', 'min:1', 'required_without:feed_url', 'prohibits:feed_url'],
            'feed_urls.*' => ['required', 'string', 'url', 'distinct'],
        ];
    }
}
