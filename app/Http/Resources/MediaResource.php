<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * An uploaded image. Conversion URLs fall back to the original file until the
 * conversion has been generated (for example while it waits on the queue).
 *
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'url' => $this->getUrl(),
            'thumb' => $this->getAvailableUrl(['thumb']),
            'large' => $this->getAvailableUrl(['large']),
        ];
    }
}
