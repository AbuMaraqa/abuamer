<?php

namespace App\Enums;

/**
 * The built-in roles. Further roles (e.g. "Sales staff") are created in the control
 * panel with the permissions they need.
 */
enum Role: string
{
    /**
     * Full access to the control panel, including users, roles and site settings.
     */
    case Admin = 'admin';

    /**
     * Manages catalog and company content.
     */
    case Editor = 'editor';

    /**
     * The permissions the role starts with. The administrator always has every permission;
     * the others can be changed in the control panel afterwards.
     *
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::Editor => [
                Permission::CategoriesView,
                Permission::CategoriesCreate,
                Permission::CategoriesUpdate,
                Permission::CategoriesDelete,
                Permission::CategoriesMove,
                Permission::ProductsView,
                Permission::ProductsCreate,
                Permission::ProductsUpdate,
                Permission::ProductsDelete,
                Permission::BrandsManage,
                Permission::CompanyManage,
                Permission::MessagesManage,
            ],
        };
    }

    /**
     * The role's name in the current language.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => __('Administrator'),
            self::Editor => __('Content editor'),
        };
    }
}
