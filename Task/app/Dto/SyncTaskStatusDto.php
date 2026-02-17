<?php

namespace Modules\Task\Dto;

/**
 * Статусы задач.
 * Синхронизация DTO.
 */
readonly class SyncTaskStatusDto
{
    /**
     * @param string $externalUuid
     * @param string $name
     */
    public function __construct(
        public string $externalUuid,
        public string $name,
    ) {}
}
