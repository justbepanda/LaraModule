<?php

namespace Modules\Task\Dto;

use Illuminate\Http\UploadedFile;

/**
 * Фотографии для задач.
 * Загрузка.
 * DTO.
 */
readonly class UploadTaskPhotoDto
{
    /**
     * @param UploadedFile[] $photos
     */
    public function __construct(
        public array $photos,
    )
    {
    }
}