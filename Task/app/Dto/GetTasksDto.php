<?php

namespace Modules\Task\Dto;

use App\Dto\PaginateDto;

readonly class GetTasksDto
{
    /**
     * @param PaginateDto $paginateDto
     * @param string|null $description
     * @param string|null $taskTypeId
     * @param string|null $vin
     * @param string|null $registrationNumber
     * @param string|null $value
     * @param string|null $companyId
     */
    public function __construct(
        public PaginateDto $paginateDto,
        public ?string     $description = null,
        public ?string     $taskTypeId = null,
        public ?string     $vin = null,
        public ?string     $registrationNumber = null,
        public ?string     $value = null,
        public ?string     $companyId = null,

    )
    {
    }
}
