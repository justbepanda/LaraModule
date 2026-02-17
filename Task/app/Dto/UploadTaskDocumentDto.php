<?php

namespace Modules\Task\Dto;

use Illuminate\Http\UploadedFile;

/**
 * Документы для задач.
 * Загрузка.
 * DTO.
 */
readonly class UploadTaskDocumentDto
{
    /**
     * @param UploadedFile[] $documents
     */
    public function __construct(
        public array  $documents,
    )
    {
    }
}