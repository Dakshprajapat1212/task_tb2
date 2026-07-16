<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchDoubtRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'chapter_id' => 'required|integer|exists:chapters,id',
            'query' => 'required|string|min:3'
        ];
    }
}
