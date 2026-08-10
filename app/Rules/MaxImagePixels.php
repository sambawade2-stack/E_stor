<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Refuse les images dont la surface en pixels est déraisonnable.
 *
 * La limite de poids ne protège de rien : une image uniforme de
 * 12 000 × 12 000 ne pèse que 0,43 Mo compressée — elle passe donc
 * « max:10240 » sans difficulté — mais sa décodification réclame environ
 * 550 Mo, et le processus est monté à plus de 1 Go lors d'un essai réel.
 *
 * Le memory_limit de PHP n'arrête pas cela : GD alloue son tampon hors du
 * gestionnaire de mémoire de PHP. Aucune exception n'est levée, le
 * conteneur est simplement tué faute de mémoire — le site entier tombe
 * pour tous les visiteurs, à cause d'un seul envoi.
 *
 * On mesure donc les dimensions AVANT tout décodage : getimagesize() ne
 * lit que l'en-tête du fichier.
 */
class MaxImagePixels implements ValidationRule
{
    private readonly int $maxPixels;

    /**
     * Par défaut : la valeur de config('shop.max_image_pixels'), soit
     * 40 Mpx — au-delà de tout appareil grand public (un cliché de
     * téléphone courant fait 12 Mpx), et ~160 Mo une fois décodé.
     */
    public function __construct(?int $maxPixels = null)
    {
        $this->maxPixels = $maxPixels ?? (int) config('shop.max_image_pixels', 40_000_000);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return;
        }

        $dimensions = @getimagesize($value->getPathname());

        // Format illisible : les règles « image » et « mimes » s'en chargent.
        if ($dimensions === false) {
            return;
        }

        [$width, $height] = $dimensions;
        $pixels = $width * $height;

        if ($pixels > $this->maxPixels) {
            $fail(sprintf(
                'L\'image est trop grande : %d × %d pixels (%.1f Mpx), maximum %d Mpx. Réduisez ses dimensions avant de l\'envoyer.',
                $width,
                $height,
                $pixels / 1_000_000,
                (int) ($this->maxPixels / 1_000_000),
            ));
        }
    }
}
