<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('role'));
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama role wajib diisi',
            'name.unique' => 'Nama role sudah terdaftar',
            'display_name.max' => 'Display name maksimal 100 karakter',
            'permissions.*.max' => 'Setiap permission maksimal 64 karakter',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Sanitize permissions
        if ($this->has('permissions') && is_array($this->permissions)) {
            $this->merge([
                'permissions' => array_filter(
                    array_map(fn ($p) => trim(strtolower($p)), $this->permissions),
                    fn ($p) => !empty($p)
                ),
            ]);
        }
    }
}
