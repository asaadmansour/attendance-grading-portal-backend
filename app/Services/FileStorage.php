<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileStorage
{
    /**
     * Store an uploaded file to the given directory on the local disk.
     * Returns the stored path or null.
     *
     * @param  \Illuminate\Http\UploadedFile|null  $file
     * @param  string  $directory
     * @return string|null
     */
    public function store(?UploadedFile $file, string $directory): ?string
    {
        if (! $file) {
            return null;
        }

        return Storage::disk('local')->putFile($directory, $file);
    }
}
