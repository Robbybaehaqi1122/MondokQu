<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SantriPhotoUploader
{
    /**
     * Store a new photo without deleting the previous file.
     */
    public function store(?UploadedFile $photo, ?string $currentPath = null): ?string
    {
        if (! $photo) {
            return $currentPath;
        }

        $directory = config('santri.photo.directory', 'santri-photos');
        return $photo->store($directory, 'public');
    }

    /**
     * Delete a previously stored photo if it belongs to our managed directory.
     */
    public function deleteIfManaged(?string $path): void
    {
        if (! $path) {
            return;
        }

        $directory = trim((string) config('santri.photo.directory', 'santri-photos'), '/').'/';

        if (str_starts_with($path, $directory) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
