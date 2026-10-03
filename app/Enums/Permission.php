<?php

namespace App\Enums;

enum Permission: string
{
    case CategoriesView = 'categories.view';
    case CategoriesCreate = 'categories.create';
    case CategoriesUpdate = 'categories.update';
    case CategoriesDelete = 'categories.delete';
    case CategoriesMove = 'categories.move';

    case ProductsView = 'products.view';
    case ProductsCreate = 'products.create';
    case ProductsUpdate = 'products.update';
    case ProductsDelete = 'products.delete';

    case BrandsManage = 'brands.manage';

    case CompanyManage = 'company.manage';
    case MessagesManage = 'messages.manage';
    case SettingsManage = 'settings.manage';

    /**
     * Staff accounts and roles. Reserved to administrators: a role that could grant it
     * would let its users give themselves any other permission.
     */
    case UsersManage = 'users.manage';

    /**
     * The permissions that can be given to a role in the control panel.
     *
     * @return list<self>
     */
    public static function assignable(): array
    {
        return array_values(array_filter(self::cases(), fn (self $permission): bool => $permission !== self::UsersManage));
    }

    /**
     * The assignable permissions by control panel area, for the role editor.
     *
     * @return list<array{group: string, permissions: list<array{name: string, label: string, requires: string|null}>}>
     */
    public static function assignableGroups(): array
    {
        return collect(self::assignable())
            ->groupBy(fn (self $permission): string => $permission->group())
            ->map(fn ($permissions, string $group): array => [
                'group' => $group,
                'permissions' => $permissions->map(fn (self $permission): array => [
                    'name' => $permission->value,
                    'label' => $permission->label(),
                    'requires' => $permission->requires()?->value,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * What the permission allows, in the current language.
     */
    public function label(): string
    {
        return match ($this) {
            self::CategoriesView => __('View categories'),
            self::CategoriesCreate => __('Add categories'),
            self::CategoriesUpdate => __('Edit categories'),
            self::CategoriesDelete => __('Delete categories'),
            self::CategoriesMove => __('Move and reorder categories'),
            self::ProductsView => __('View products'),
            self::ProductsCreate => __('Add products'),
            self::ProductsUpdate => __('Edit products'),
            self::ProductsDelete => __('Delete products'),
            self::BrandsManage => __('Manage brands'),
            self::CompanyManage => __('Edit company content and the home page slider'),
            self::MessagesManage => __('Read and manage contact messages'),
            self::SettingsManage => __('Change contact details, SEO and website settings'),
            self::UsersManage => __('Manage users and roles'),
        };
    }

    /**
     * The area of the control panel the permission belongs to, in the current language.
     */
    public function group(): string
    {
        return match ($this) {
            self::CategoriesView, self::CategoriesCreate, self::CategoriesUpdate, self::CategoriesDelete, self::CategoriesMove => __('Categories'),
            self::ProductsView, self::ProductsCreate, self::ProductsUpdate, self::ProductsDelete, self::BrandsManage => __('Products and brands'),
            self::CompanyManage, self::SettingsManage => __('Website'),
            self::MessagesManage => __('Messages'),
            self::UsersManage => __('Administration'),
        };
    }

    /**
     * The permission this one needs to be useful: changing products starts from the product list.
     */
    public function requires(): ?self
    {
        return match ($this) {
            self::CategoriesCreate, self::CategoriesUpdate, self::CategoriesDelete, self::CategoriesMove => self::CategoriesView,
            self::ProductsCreate, self::ProductsUpdate, self::ProductsDelete => self::ProductsView,
            default => null,
        };
    }
}
