<?php

namespace App\Http\Requests\Admin;

use App\Models\Slide;

class UpdateSlideRequest extends StoreSlideRequest
{
    /**
     * An existing slide keeps its image unless a new one is uploaded.
     */
    protected function imageRequired(): bool
    {
        /** @var Slide $slide */
        $slide = $this->route('slide');

        return $slide->getFirstMedia(Slide::IMAGE_COLLECTION) === null;
    }
}
