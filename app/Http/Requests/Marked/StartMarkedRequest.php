<?php

namespace App\Http\Requests\Marked;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StartMarkedRequest extends FormRequest
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
            'owner' => ['required', 'integer'],
            'type' => ['required', 'string'],
            'date_interval' => ['nullable', 'array'],
            'call_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * Validation errors messages
     *
     * @return array|void
     */
    public function messages()
    {
        return [
            'owner.integer' => 'Выберите значение из списка',
            'required' => 'Поле обязательно к заполнению',
            'call_id.integer' => 'Звонок не может быть пустым',
            'date_interval.array' => 'Выберите дату',
        ];
    }
}
