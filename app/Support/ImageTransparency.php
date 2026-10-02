<?php

namespace App\Support;

final class ImageTransparency
{
    /**
     * Whether the image has see-through areas, sampled on a grid. A transparent logo can be
     * turned white for dark backgrounds; an opaque one (a photo or a logo on a solid
     * background) would become a plain white rectangle.
     */
    public static function detect(string $path): bool
    {
        $contents = is_file($path) ? file_get_contents($path) : false;
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            return false;
        }

        [$width, $height] = [imagesx($image), imagesy($image)];
        $steps = 40;

        for ($column = 0; $column <= $steps; $column++) {
            for ($row = 0; $row <= $steps; $row++) {
                $x = (int) min($width - 1, $width * $column / $steps);
                $y = (int) min($height - 1, $height * $row / $steps);

                // GD alpha runs from 0 (opaque) to 127 (fully transparent).
                if (imagecolorsforindex($image, imagecolorat($image, $x, $y))['alpha'] > 64) {
                    imagedestroy($image);

                    return true;
                }
            }
        }

        imagedestroy($image);

        return false;
    }
}
