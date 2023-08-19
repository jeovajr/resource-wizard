<?php

namespace ResourceWizard\Requests;

use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;

abstract class FormRequest extends IlluminateFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Must implement the authorization here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return match ($this->method()) {
            'POST' => $this->getStoreRules(),
            'PATCH', 'PUT' => $this->getUpdateRules(),
            default => [],
        };
    }

    /**
     * Get the rules used on store
     */
    protected function getStoreRules(): array
    {
        return [];
    }

    /**
     * Get the rules used on update
     */
    protected function getUpdateRules(): array
    {
        return [];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [];
    }
}
