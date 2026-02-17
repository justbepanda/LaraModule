<?php

namespace Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Task\Dto\UploadTaskDocumentDto;

/**
 * Документы для задач.
 * Загрузка.
 * DTO.
 */
class UploadTaskDocumentRequest extends FormRequest
{
    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'documents' => [
                'required',
                'array',
                'min:1',
            ],
            'documents.*' => [
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,txt,rtf,csv',
                'max:10240',
            ],
        ];
    }

    /**
     * @return UploadTaskDocumentDto
     */
    public function toDto(): UploadTaskDocumentDto
    {
        return new UploadTaskDocumentDto(
            documents: $this->file('documents'),
        );
    }
}
