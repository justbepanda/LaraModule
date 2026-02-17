<?php

namespace Modules\Task\Dto;

use Modules\Technique\Enums\TechniqueMileageType;

class CreateTaskDto
{
    /**
     * @param string $companyId
     * @param string $taskTypeId
     * @param string|null $techniqueId
     * @param string|null $address
     * @param float|null $mileageValue
     * @param TechniqueMileageType $mileageType
     * @param string|null $description
     * @param string $authorId
     */
    public function __construct(
        public readonly string               $companyId,
        public readonly string               $taskTypeId,
        public readonly ?string              $techniqueId,
        public readonly ?string              $address,
        public readonly ?float               $mileageValue,
        public readonly TechniqueMileageType $mileageType,
        public readonly ?string              $description,
        public readonly string               $authorId
    )
    {

    }
}