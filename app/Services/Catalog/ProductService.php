<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Persists the complete state submitted by the product form: attributes and
 * translations, the ordered specifications, and the main image and gallery.
 */
class ProductService
{
    /**
     * @param  array<string, mixed>  $attributes  Product columns and translations keyed by locale.
     * @param  list<array{id: int|null, translations: array<string, array{label: string, value: string}>}>  $specifications
     * @param  list<int>  $keptGalleryIds  Existing gallery images to keep, in display order.
     * @param  list<UploadedFile>  $galleryUploads  New gallery images, appended after the kept ones.
     */
    public function save(
        Product $product,
        array $attributes,
        array $specifications,
        ?UploadedFile $mainImage = null,
        bool $removeMainImage = false,
        array $keptGalleryIds = [],
        array $galleryUploads = [],
    ): Product {
        DB::transaction(function () use ($product, $attributes, $specifications) {
            $product->fill($attributes)->save();

            $this->syncSpecifications($product, $specifications);
        });

        $this->syncMainImage($product, $mainImage, $removeMainImage);
        $this->syncGallery($product, $keptGalleryIds, $galleryUploads);

        return $product;
    }

    /**
     * Update kept specifications in their new order, create new ones and delete the rest.
     *
     * @param  list<array{id: int|null, translations: array<string, array{label: string, value: string}>}>  $specifications
     */
    private function syncSpecifications(Product $product, array $specifications): void
    {
        $existing = $product->specifications()->with('translations')->get()->keyBy('id');
        $keptIds = [];

        foreach ($specifications as $index => $specification) {
            // Ids that do not belong to this product are treated as new rows.
            $model = $existing->get($specification['id'] ?? 0) ?? $product->specifications()->make();

            $model->fill(['sort_order' => $index + 1, ...$specification['translations']])->save();
            $keptIds[] = $model->id;
        }

        $product->specifications()->whereKeyNot($keptIds)->delete();
        $product->unsetRelation('specifications');
    }

    private function syncMainImage(Product $product, ?UploadedFile $mainImage, bool $removeMainImage): void
    {
        if ($mainImage !== null) {
            $product->addMedia($mainImage)->toMediaCollection(Product::MAIN_IMAGE_COLLECTION);
        } elseif ($removeMainImage) {
            $product->clearMediaCollection(Product::MAIN_IMAGE_COLLECTION);
        }
    }

    /**
     * @param  list<int>  $keptIds
     * @param  list<UploadedFile>  $uploads
     */
    private function syncGallery(Product $product, array $keptIds, array $uploads): void
    {
        $gallery = $product->getMedia(Product::GALLERY_COLLECTION);

        // Only ids that already belong to this product's gallery are honoured.
        $keptIds = array_values(array_intersect($keptIds, $gallery->pluck('id')->all()));

        $gallery->whereNotIn('id', $keptIds)->each(fn (Media $media) => $media->delete());

        foreach ($uploads as $upload) {
            $keptIds[] = $product->addMedia($upload)->toMediaCollection(Product::GALLERY_COLLECTION)->id;
        }

        Media::setNewOrder($keptIds);
        $product->unsetRelation('media');
    }
}
