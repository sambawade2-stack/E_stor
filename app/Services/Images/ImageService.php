<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use RuntimeException;

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
        $this->guardAgainstOversizedDimensions($file);

        $image = ImageManager::usingDriver(Driver::class)
            ->decodePath($file->getPathname())
            ->scaleDown(width: $maxWidth);

        $path = trim($directory, '/').'/'.Str::uuid()->toString().'.webp';

        Storage::disk('public')->put($path, (string) $image->encode(new WebpEncoder(quality: $quality)));

        return $path;
    }

    /**
     * Mesure les dimensions sans décoder l'image : getimagesize() ne lit
     * que l'en-tête du fichier, pour quelques octets.
     *
     * @throws RuntimeException si la surface dépasse ce que la machine peut absorber
     */
    private function guardAgainstOversizedDimensions(UploadedFile $file): void
    {
        $dimensions = @getimagesize($file->getPathname());

        if ($dimensions === false) {
            return; // Format illisible : le décodage échouera plus bas, proprement.
        }

        [$width, $height] = $dimensions;
        $maxPixels = (int) config('shop.max_image_pixels', 40_000_000);

        if ($width * $height > $maxPixels) {
            throw new RuntimeException(sprintf(
                'Image trop grande : %d × %d pixels, maximum %d Mpx.',
                $width,
                $height,
                (int) ($maxPixels / 1_000_000),
            ));
        }
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
