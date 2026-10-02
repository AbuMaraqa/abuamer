<?php

namespace App\Enums;

/**
 * Where a slide's button leads.
 */
enum SlideLinkType: string
{
    /**
     * No button.
     */
    case None = 'none';

    /**
     * A category page; its URL is built in each language from the category tree.
     */
    case Category = 'category';

    /**
     * A link entered for each language.
     */
    case Custom = 'custom';
}
