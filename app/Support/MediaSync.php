<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Applies the image state submitted by a control panel form to a media collection.
 */
final class MediaSync
{
    /**
     * Replace a single-file collection with the upload, or clear it when requested.
     */
    public static function single(Model&HasMedia $model, string $collection, ?UploadedFile $upload, bool $remove = false): void
    {
        if ($upload !== null) {
            $model->addMedia($upload)->toMediaCollection($collection);
        } elseif ($remove) {
            $model->clearMediaCollection($collection);
        }

        $model->unsetRelation('media');
    }

    /**
     * Keep the listed images in the given order, delete the others, then append the uploads.
     * Ids that do not belong to this model's collection are ignored.
     *
     * @param  list<int>  $keptIds
     * @param  list<UploadedFile>  $uploads
     */
    public static function gallery(Model&HasMedia $model, string $collection, array $keptIds, array $uploads): void
    {
        $current = $model->getMedia($collection);
        $keptIds = array_values(array_intersect($keptIds, $current->pluck('id')->all()));

        $current->whereNotIn('id', $keptIds)->each(fn (Media $media) => $media->delete());

        foreach ($uploads as $upload) {
            $keptIds[] = $model->addMedia($upload)->toMediaCollection($collection)->id;
        }

        Media::setNewOrder($keptIds);
        $model->unsetRelation('media');
    }
}
