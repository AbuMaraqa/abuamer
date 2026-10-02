<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Products Shown on a Category Page
    |--------------------------------------------------------------------------
    |
    | When true, a category page lists the products of the category and of all
    | its (visible) descendants, so intermediate categories such as "Porcelain"
    | never look empty. When false, only products assigned directly to the
    | category are listed.
    |
    */

    'include_descendant_products' => (bool) env('CATALOG_INCLUDE_DESCENDANT_PRODUCTS', true),

    /*
    |--------------------------------------------------------------------------
    | Page Sizes
    |--------------------------------------------------------------------------
    */

    'per_page' => 12,

    'admin_per_page' => 20,

];
