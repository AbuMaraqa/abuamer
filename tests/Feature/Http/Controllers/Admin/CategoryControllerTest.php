<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function categoryPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'parent_id' => null,
        'status' => true,
        'ar' => ['name' => 'بورسلان', 'slug' => '', 'description' => 'مجموعة البورسلان الفاخرة'],
        'en' => ['name' => 'Porcelain', 'slug' => '', 'description' => 'Premium porcelain collection'],
    ], $overrides);
}

describe('index', function () {
    it('renders the whole tree with product counts', function () {
        $porcelain = Category::factory()->named('Porcelain', 'بورسلان')->create(['sort_order' => 1]);
        $categories = createCategoryTree(['Marble Effect' => ['Calacatta' => []]], $porcelain);
        Category::factory()->named('Wall Tiles')->create(['sort_order' => 2]);
        Product::factory()->count(2)->for($categories['Calacatta'])->create();

        $response = $this->actingAs(admin())->get(route('admin.categories.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Categories/Index')
            ->has('tree', 2)
            ->where('tree.0.name', 'بورسلان')
            ->where('tree.0.names.en', 'Porcelain')
            ->where('tree.0.children.0.children.0.id', $categories['Calacatta']->id)
            ->where('tree.0.children.0.children.0.products_count', 2));
    });

    it('forbids users who cannot view categories', function () {
        $response = $this->actingAs(userWithPermissions())->get(route('admin.categories.index'));

        $response->assertForbidden();
    });
});

describe('create', function () {
    it('preselects the parent for a new child category', function () {
        $parent = Category::factory()->create();

        $response = $this->actingAs(admin())->get(route('admin.categories.create', ['parent_id' => $parent->id]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Categories/Create')
            ->where('parentId', $parent->id));
    });
});

describe('store', function () {
    it('creates a root category in both languages', function () {
        $response = $this->actingAs(admin())->post(route('admin.categories.store'), categoryPayload());

        $category = Category::sole();
        $response->assertRedirect(route('admin.categories.index', ['highlight' => $category->id]))
            ->assertInertiaFlash('toast.type', 'success');
        expect($category->parent_id)->toBeNull()
            ->and($category->translate('ar')->name)->toBe('بورسلان')
            ->and($category->translate('ar')->slug)->toBe('بورسلان')
            ->and($category->translate('en')->slug)->toBe('porcelain')
            ->and($category->translate('en')->description)->toBe('Premium porcelain collection');
    });

    it('appends a child category after its existing siblings', function () {
        $categories = createCategoryTree(['Porcelain' => ['Stone Effect' => [], 'Wood Effect' => []]]);

        $this->actingAs(admin())->post(route('admin.categories.store'), categoryPayload([
            'parent_id' => $categories['Porcelain']->id,
            'ar' => ['name' => 'تأثير الرخام'],
            'en' => ['name' => 'Marble Effect'],
        ]));

        $child = Category::query()->whereTranslation('name', 'Marble Effect', 'en')->sole();
        expect($child->parent_id)->toBe($categories['Porcelain']->id)
            ->and($child->sort_order)->toBe(3);
    });

    it('creates a category at any depth', function () {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => ['Calacatta' => []]]]);

        $this->actingAs(admin())->post(route('admin.categories.store'), categoryPayload([
            'parent_id' => $categories['Calacatta']->id,
            'ar' => ['name' => 'ذهبي'],
            'en' => ['name' => 'Gold'],
        ]));

        $gold = Category::query()->whereTranslation('name', 'Gold', 'en')->sole();
        expect($gold->parent_id)->toBe($categories['Calacatta']->id);
    });

    it('normalises a typed slug', function () {
        $this->actingAs(admin())->post(route('admin.categories.store'), categoryPayload([
            'ar' => ['slug' => 'بورسلان فاخر'],
            'en' => ['slug' => 'Fine Porcelain'],
        ]));

        expect(Category::sole()->translate('ar')->slug)->toBe('بورسلان-فاخر')
            ->and(Category::sole()->translate('en')->slug)->toBe('fine-porcelain');
    });

    it('rejects a slug already used by a sibling', function () {
        $parent = Category::factory()->create();
        Category::factory()->named('Porcelain')->childOf($parent)->create();

        $response = $this->actingAs(admin())->post(route('admin.categories.store'), categoryPayload(['parent_id' => $parent->id]));

        $response->assertSessionHasErrors(['en.slug' => 'يوجد تصنيف آخر بالرابط نفسه تحت التصنيف الأب ذاته.']);
        expect(Category::count())->toBe(2);
    });

    it('allows the same slug under a different parent', function () {
        $categories = createCategoryTree(['Marble' => ['White' => []], 'Calacatta' => []]);

        $response = $this->actingAs(admin())->post(route('admin.categories.store'), categoryPayload([
            'parent_id' => $categories['Calacatta']->id,
            'ar' => ['name' => 'White'],
            'en' => ['name' => 'White'],
        ]));

        $response->assertSessionHasNoErrors();
        expect(Category::query()->whereTranslation('slug', 'white', 'en')->count())->toBe(2);
    });

    it('requires a name in both languages', function () {
        $response = $this->actingAs(admin())->post(route('admin.categories.store'), []);

        $response->assertSessionHasErrors(['ar.name', 'en.name', 'status']);
        expect(Category::count())->toBe(0);
    });

    it('reports validation errors in Arabic with readable field names', function () {
        $response = $this->actingAs(admin())->post(route('admin.categories.store'), categoryPayload(['en' => ['name' => '']]));

        $response->assertSessionHasErrors(['en.name' => 'حقل الاسم (الإنجليزية) مطلوب.']);
    });

    it('stores the uploaded image in the category image collection', function () {
        Storage::fake('public');

        $this->actingAs(admin())->post(route('admin.categories.store'), categoryPayload([
            'image' => UploadedFile::fake()->image('porcelain.jpg', 1200, 800),
        ]));

        $media = Category::sole()->getFirstMedia(Category::IMAGE_COLLECTION);
        expect($media->file_name)->toBe('porcelain.jpg');
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
    });

    it('rejects a file that is not an image', function () {
        Storage::fake('public');

        $response = $this->actingAs(admin())->post(route('admin.categories.store'), categoryPayload([
            'image' => UploadedFile::fake()->create('catalog.pdf', 100, 'application/pdf'),
        ]));

        $response->assertSessionHasErrors('image');
        expect(Category::count())->toBe(0);
    });

    it('forbids users who cannot create categories', function () {
        $response = $this->actingAs(userWithPermissions(Permission::CategoriesView))
            ->post(route('admin.categories.store'), categoryPayload());

        $response->assertForbidden();
        expect(Category::count())->toBe(0);
    });
});

describe('update', function () {
    it('updates the translations and status', function () {
        $category = Category::factory()->named('Porcelain', 'بورسلان')->create();

        $response = $this->actingAs(admin())->put(route('admin.categories.update', $category), categoryPayload([
            'status' => false,
            'ar' => ['name' => 'بورسلان فاخر', 'slug' => 'بورسلان-فاخر'],
            'en' => ['name' => 'Fine Porcelain', 'slug' => 'fine-porcelain'],
        ]));

        $response->assertRedirect(route('admin.categories.index', ['highlight' => $category->id]));
        $category->refresh();
        expect($category->status)->toBeFalse()
            ->and($category->translate('ar')->slug)->toBe('بورسلان-فاخر')
            ->and($category->translate('en')->name)->toBe('Fine Porcelain');
    });

    it('keeps its own slug without reporting a clash with itself', function () {
        $category = Category::factory()->named('Porcelain', 'بورسلان')->create();

        $response = $this->actingAs(admin())->put(route('admin.categories.update', $category), categoryPayload());

        $response->assertSessionHasNoErrors();
    });

    it('moves the category when another parent is selected', function () {
        $categories = createCategoryTree(['Floor Tiles' => ['Marble' => []], 'Porcelain' => ['Stone Effect' => []]]);

        $this->actingAs(admin())->put(route('admin.categories.update', $categories['Marble']), categoryPayload([
            'parent_id' => $categories['Porcelain']->id,
            'ar' => ['name' => 'Marble'],
            'en' => ['name' => 'Marble'],
        ]));

        expect($categories['Marble']->fresh()->parent_id)->toBe($categories['Porcelain']->id)
            ->and($categories['Marble']->fresh()->sort_order)->toBe(2);
    });

    it('refuses one of its own descendants as the new parent', function () {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []]]);

        $response = $this->actingAs(admin())->put(route('admin.categories.update', $categories['Porcelain']), categoryPayload([
            'parent_id' => $categories['Marble Effect']->id,
        ]));

        $response->assertSessionHasErrors('parent_id');
        expect($categories['Porcelain']->fresh()->parent_id)->toBeNull()
            ->and($categories['Porcelain']->fresh()->translate('en')->name)->toBe('Porcelain');
    });

    it('removes the image when requested', function () {
        Storage::fake('public');
        $category = Category::factory()->named('Porcelain', 'بورسلان')->create();
        $category->addMedia(UploadedFile::fake()->image('old.jpg', 800, 600))->toMediaCollection(Category::IMAGE_COLLECTION);

        $this->actingAs(admin())->put(route('admin.categories.update', $category), categoryPayload(['remove_image' => true]));

        expect($category->fresh()->getFirstMedia(Category::IMAGE_COLLECTION))->toBeNull();
    });
});

describe('destroy', function () {
    it('deletes a category without children', function () {
        $category = Category::factory()->create();

        $response = $this->actingAs(admin())->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertModelMissing($category);
    });

    it('refuses to delete a category that has child categories', function () {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []]]);

        $response = $this->actingAs(admin())->delete(route('admin.categories.destroy', $categories['Porcelain']));

        $response->assertSessionHasErrors(['category' => 'لا يمكن حذف التصنيف لأنه يحتوي على تصنيفات فرعية.']);
        $this->assertModelExists($categories['Marble Effect']);
    });

    it('deletes the descendants when explicitly requested', function () {
        $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []]]);

        $this->actingAs(admin())->delete(route('admin.categories.destroy', $categories['Porcelain']), ['with_descendants' => true]);

        expect(Category::count())->toBe(0);
    });

    it('forbids users who cannot delete categories', function () {
        $category = Category::factory()->create();

        $response = $this->actingAs(userWithPermissions(Permission::CategoriesView))->delete(route('admin.categories.destroy', $category));

        $response->assertForbidden();
        $this->assertModelExists($category);
    });
});
