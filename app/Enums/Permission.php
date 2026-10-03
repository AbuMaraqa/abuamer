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
}
