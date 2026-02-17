<?php

namespace Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Task\Dto\UpdateTaskDto;
use Modules\Technique\Enums\TechniqueMileageType;


class UpdateTaskRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'company_id' => [
                'required',
                'uuid',
                'exists:companies,id',
            ],
            'task_type_id' => [
                'required',
                'exists:task_types,id',
            ],
            'technique_id' => [
                'required',
                'exists:techniques,id',
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
            'mileage.value' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'mileage.type' => [
                'required',
                Rule::in(TechniqueMileageType::values())
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }


    /**
     * @return UpdateTaskDto
     */
    public function toDto(): UpdateTaskDto
    {
        $mileage = $this->input('mileage', []);

        return new UpdateTaskDto(
            companyId: $this->input('company_id'),
            taskTypeId: $this->input('task_type_id'),
            techniqueId: $this->input('technique_id'),
            address: $this->input('address'),
            mileageValue: $mileage['value'] ?? null,
            mileageType: isset($mileage['type']) ? TechniqueMileageType::from($mileage['type']) : null,
            description: $this->input('description'),
        );
    }

}