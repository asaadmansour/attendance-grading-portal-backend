<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileStorage
{
    public function store(?UploadedFile $file, string $directory, string $disk = 'local'): ?string
    {
        if (! $file) {
            return null;
        }
        return Storage::disk($disk)->putFile($directory, $file);
    }
}