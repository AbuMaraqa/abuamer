<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A downloadable file, such as a product's technical data sheet.
 *
 * @mixin Media
 */
class DocumentResource extends JsonResource
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
            'name' => $this->name,
            'file_name' => $this->file_name,
            'extension' => mb_strtoupper($this->extension),
            'size' => $this->human_readable_size,
            'url' => $this->getUrl(),
        ];
    }
}
