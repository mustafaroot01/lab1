<?php

namespace App\Enums\Chat;

enum AttachmentType: string
{
    case Image = 'image';
    case Pdf = 'pdf';

    public static function fromExtension(string $extension): self
    {
        $extension = strtolower($extension);

        return $extension === 'pdf' ? self::Pdf : self::Image;
    }
}
