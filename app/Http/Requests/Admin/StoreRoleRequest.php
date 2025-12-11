<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Role::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
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

        // Default is_active to true
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
