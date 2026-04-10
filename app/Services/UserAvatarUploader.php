<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserAvatarUploader
{
    /**
     * Store a new avatar and optionally replace the previous managed file.
     */
    public function store(?UploadedFile $avatar, ?string $currentPath = null): ?string
    {
        if (! $avatar) {
            return $currentPath;
        }

        $directory = config('user.avatar.directory', 'avatars');
        $storedPath = $avatar->store($directory, 'public');

        $this->deleteIfManaged($currentPath);

        return $storedPath;
    }

    /**
     * Delete a previously stored avatar if it belongs to our managed directory.
     */
    public function deleteIfManaged(?string $path): void
    {
        if (! $path) {
            return;
        }

        $directory = trim((string) config('user.avatar.directory', 'avatars'), '/').'/';

        if (str_starts_with($path, $directory) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
