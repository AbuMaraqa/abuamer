<?php

use App\Support\ImageTransparency;

/**
 * Write a 200 × 80 PNG with a dark mark in the middle, on a transparent or solid background.
 */
function logoFixture(bool $transparentBackground): string
{
    $image = imagecreatetruecolor(200, 80);
    imagesavealpha($image, true);
    imagealphablending($image, false);
    imagefill($image, 0, 0, $transparentBackground ? imagecolorallocatealpha($image, 0, 0, 0, 127) : imagecolorallocate($image, 255, 255, 255));
    imagefilledrectangle($image, 60, 20, 140, 60, imagecolorallocate($image, 20, 20, 20));

    $path = tempnam(sys_get_temp_dir(), 'logo').'.png';
    imagepng($image, $path);
    imagedestroy($image);

    return $path;
}

it('detects a logo on a transparent background', function () {
    expect(ImageTransparency::detect(logoFixture(transparentBackground: true)))->toBeTrue();
});

it('treats a logo on a solid background as opaque', function () {
    expect(ImageTransparency::detect(logoFixture(transparentBackground: false)))->toBeFalse();
});

it('treats a missing or unreadable file as opaque', function () {
    expect(ImageTransparency::detect(sys_get_temp_dir().'/missing-logo.png'))->toBeFalse();
});
