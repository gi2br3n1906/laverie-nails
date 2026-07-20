<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\SizeStandard;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSizeStandardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) === true;
    }

    /**
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        /** @var SizeStandard $sizeStandard */
        $sizeStandard = $this->route('size_standard');

        return [
            'size_name' => [
                'required',
                'string',
                Rule::in(['XS', 'S', 'M', 'L']),
                Rule::unique('size_standards', 'size_name')->ignore($sizeStandard),
            ],
            'jempol' => $this->millimeterRules(),
            'telunjuk' => $this->millimeterRules(),
            'tengah' => $this->millimeterRules(),
            'manis' => $this->millimeterRules(),
            'kelingking' => $this->millimeterRules(),
            'tolerance' => $this->millimeterRules(),
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'size_name' => mb_strtoupper(trim($this->string('size_name')->toString())),
            'is_active' => $this->has('is_active') ? $this->input('is_active') : false,
        ]);
    }

    /**
     * @return list<string>
     */
    private function millimeterRules(): array
    {
        return ['required', 'numeric', 'min:0', 'max:25', 'decimal:0,1'];
    }
}
