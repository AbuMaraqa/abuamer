<?php

namespace App\Http\Resources;

use App\Models\CompanyHighlight;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CompanyHighlight
 */
class CompanyHighlightResource extends JsonResource
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
            'icon' => $this->icon,
            'value' => $this->value,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
