<?php

namespace Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Task\Dto\UploadTaskPhotoDto;

/**
 * Фотографии для задач.
 * Загрузка.
 * DTO.
 */
class UploadTaskPhotoRequest extends FormRequest
{
    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'photos' => [
                'required',
                'array',
                'min:1',
            ],
            'photos.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,heic,heif',
                'max:10240',
            ],
        ];
    }

    /**
     * @return UploadTaskPhotoDto
     */
    public function toDto(): UploadTaskPhotoDto
    {
        return new UploadTaskPhotoDto(
            photos: $this->file('photos'),
        );
    }
}
