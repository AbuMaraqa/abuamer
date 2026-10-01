<?php

namespace App\Enums;

enum Role: string
{
    /**
     * Full access to the control panel, including users and site settings.
     */
    case Admin = 'admin';

    /**
     * Manages catalog and company content.
     */
    case Editor = 'editor';

    /**
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
                Permission::CompanyManage,
            ],
        };
    }
}
