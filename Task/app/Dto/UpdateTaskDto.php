<?php

namespace Modules\Task\Dto;

use Modules\Technique\Enums\TechniqueMileageType;

readonly class UpdateTaskDto
{
    /**
     * @param string $companyId
     * @param string $taskTypeId
     * @param string $techniqueId
     * @param string|null $address
     * @param float|null $mileageValue
     * @param TechniqueMileageType $mileageType
     * @param string|null $description
     */
    public function __construct(
        public string               $companyId,
        public string               $taskTypeId,
        public string               $techniqueId,
        public ?string              $address,
        public ?float               $mileageValue,
        public TechniqueMileageType $mileageType,
        public ?string              $description,
    )
    {
    }
}