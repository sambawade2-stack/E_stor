<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    /**
     * Optimise et enregistre une image sur le disque public :
     * redimensionnée à $maxWidth maximum, recodée en WebP.
     *
     * @return string le chemin relatif enregistré
     */
    public function store(UploadedFile $file, string $directory, int $maxWidth = 1200, int $quality = 82): string
    {
        $image = ImageManager::withDriver(Driver::class)
            ->read($file->getPathname())
            ->scaleDown(width: $maxWidth);

        $path = trim($directory, '/').'/'.Str::uuid()->toString().'.webp';

        Storage::disk('public')->put($path, (string) $image->toWebp($quality));

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
