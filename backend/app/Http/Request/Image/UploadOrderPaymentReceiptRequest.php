<?php

namespace HiEvents\Http\Request\Image;

use Illuminate\Foundation\Http\FormRequest;

class UploadOrderPaymentReceiptRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'max:8192',
                'mimes:jpeg,png,jpg,webp,pdf',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => __('Please select an image to upload.'),
            'image.image' => __('The file must be an image.'),
            'image.max' => __('The image must be less than 8MB.'),
            'image.mimes' => __('The image must be a JPEG, PNG, JPG, or WebP file.'),
        ];
    }
}
