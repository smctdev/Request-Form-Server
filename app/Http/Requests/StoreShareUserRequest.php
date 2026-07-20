<?php

namespace App\Http\Requests;

use App\Models\RequestForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreShareUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && RequestForm::query()
            ->where('id', $this->request_id)
            ->where('user_id', Auth::id())
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_ids'   => ['required', 'array'],
            'user_ids.*' => ['required', Rule::exists('users', 'id')],
            'request_id' => ['required', Rule::exists('request_forms', 'id')]
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required'   => 'Please select at least one user',
        ];
    }
}
