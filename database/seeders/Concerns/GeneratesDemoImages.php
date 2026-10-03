<?php

namespace Database\Seeders\Concerns;

use GdImage;

/**
 * Generated surfaces that stand in for real photography in the demo content. Real
 * photographs are uploaded from the control panel.
 */
trait GeneratesDemoImages
{
    /**
     * A large-format tile surface with marble-like veins and soft light, saved as a temporary JPEG.
     *
     * @param  array{int, int, int}  $dark
     * @param  array{int, int, int}  $light
     */
    protected function tileTexture(array $dark, array $light, int $seed): string
    {
        mt_srand($seed);
        [$width, $height, $tile] = [2400, 1350, 600];

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);

        // Grout between the tiles.
        imagefill($image, 0, 0, imagecolorallocate($image, ...array_map(fn (int $channel): int => (int) ($channel * 0.55), $dark)));

        for ($x = 0; $x < $width; $x += $tile) {
            for ($y = -$tile / 3; $y < $height; $y += $tile) {
                $mix = 0.35 + mt_rand(0, 12) / 100;
                $color = array_map(fn (int $from, int $to): int => (int) ($from + ($to - $from) * $mix), $dark, $light);
                imagefilledrectangle($image, $x + 2, (int) $y + 2, $x + $tile - 2, (int) $y + $tile - 2, imagecolorallocate($image, ...$color));
            }
        }

        // Veins: wandering lines in light, translucent strokes.
        for ($vein = 0; $vein < 18; $vein++) {
            [$x, $y] = [mt_rand(-300, $width), mt_rand(0, $height)];
            $angle = deg2rad(mt_rand(-35, 35));
            imagesetthickness($image, mt_rand(1, 3));
            $color = imagecolorallocatealpha($image, ...[...$light, mt_rand(70, 110)]);

            for ($step = 0; $step < 90; $step++) {
                $angle += deg2rad(mt_rand(-14, 14));
                [$nextX, $nextY] = [$x + cos($angle) * 22, $y + sin($angle) * 22];
                imageline($image, (int) $x, (int) $y, (int) $nextX, (int) $nextY, $color);
                [$x, $y] = [$nextX, $nextY];
            }
        }

        $this->addSoftLight($image, $width, $height);

        return $this->saveJpeg($image, 'texture');
    }

    /**
     * A bathroom wall of slim, vertically stacked tiles above a stone floor, with soft
     * light from the side, saved as a temporary JPEG.
     *
     * @param  array{int, int, int}  $wall
     * @param  array{int, int, int}  $floor
     */
    protected function bathroomTexture(array $wall, array $floor, int $seed): string
    {
        mt_srand($seed);
        [$width, $height] = [2400, 1350];
        [$tileWidth, $tileHeight, $floorTop] = [150, 450, (int) ($height * 0.78)];

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);

        // Grout lines, a shade darker than the wall.
        imagefill($image, 0, 0, imagecolorallocate($image, ...array_map(fn (int $channel): int => (int) ($channel * 0.82), $wall)));

        for ($x = 0; $x < $width; $x += $tileWidth) {
            for ($y = $floorTop - $tileHeight; $y > -$tileHeight; $y -= $tileHeight) {
                $shade = 0.94 + mt_rand(0, 8) / 100;
                $color = array_map(fn (int $channel): int => min(255, (int) ($channel * $shade)), $wall);
                imagefilledrectangle($image, $x + 2, max(0, $y + 2), $x + $tileWidth - 2, $y + $tileHeight - 2, imagecolorallocate($image, ...$color));
            }
        }

        // The floor: large stone slabs, darker towards the front.
        for ($y = $floorTop; $y < $height; $y++) {
            $depth = ($y - $floorTop) / ($height - $floorTop);
            $color = array_map(fn (int $channel): int => (int) ($channel * (1 - 0.35 * $depth)), $floor);
            imageline($image, 0, $y, $width, $y, imagecolorallocate($image, ...$color));
        }

        $joint = imagecolorallocatealpha($image, 0, 0, 0, 100);

        for ($x = -400; $x < $width; $x += 800) {
            imageline($image, $x, $floorTop, $x - 260, $height, $joint);
        }

        // A soft shadow where the wall meets the floor.
        for ($offset = 0; $offset < 60; $offset++) {
            imageline($image, 0, $floorTop - $offset, $width, $floorTop - $offset, imagecolorallocatealpha($image, 0, 0, 0, 127 - (int) (40 * (1 - $offset / 60))));
        }

        $this->addSoftLight($image, $width, $height);

        return $this->saveJpeg($image, 'bathroom');
    }

    /**
     * Light falling from the top corner.
     */
    private function addSoftLight(GdImage $image, int $width, int $height): void
    {
        for ($ring = 40; $ring > 0; $ring--) {
            $glow = imagecolorallocatealpha($image, 255, 246, 230, 127 - (int) (3 * (40 - $ring) / 40 * 3));
            imagefilledellipse($image, (int) ($width * 0.75), (int) ($height * 0.1), $ring * 90, $ring * 70, $glow);
        }
    }

    private function saveJpeg(GdImage $image, string $prefix): string
    {
        $path = tempnam(sys_get_temp_dir(), $prefix).'.jpg';
        imagejpeg($image, $path, 88);
        imagedestroy($image);

        return $path;
    }
}
