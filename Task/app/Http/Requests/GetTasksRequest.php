<?php

namespace Modules\Task\Http\Requests;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\Traits\FilterableRequest;
use Modules\Task\Dto\GetTasksDto;

class GetTasksRequest extends PaginationRequest
{
    use FilterableRequest;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            ...self::filtersRules([
                'company_id' => ['string', 'uuid', 'exists:companies,id'],
                'description' => ['nullable', 'string', 'max:255'],
                'task_type_id' => ['nullable', 'exists:task_types,id'],
                'vin' => ['nullable', 'string', 'max:255'],
                'registration_number' => ['nullable', 'string', 'max:255'],
                'value' => ['sometimes', 'string', 'max:255',],
            ]),
        ];
    }

    /**
     * @return GetTasksDto
     */
    public function toDto(): GetTasksDto
    {
        $companyId = $this->getFilterValidatedParam('company_id');

        // Если текущий пользователь не суперадмин, используем его компанию
        $user = $this->user();
        if (!$user->isSuperadministrator()) {
            $companyId = $user->company_id;
        }

        return new GetTasksDto(
            paginateDto: $this->getPaginateDto(),
            description: $this->getFilterValidatedParam('description'),
            taskTypeId: $this->getFilterValidatedParam('task_type_id'),
            vin: $this->getFilterValidatedParam('vin'),
            registrationNumber: $this->getFilterValidatedParam('registration_number'),
            value: $this->getFilterValidatedParam('value'),
            companyId: $companyId
        );
    }
}
