<?php

namespace App\Enums;

enum HighlightType: string
{
    /**
     * A company value, e.g. "Quality".
     */
    case Value = 'value';

    /**
     * A reason to choose the company ("Why Nasaq"), shown with an icon.
     */
    case Feature = 'feature';

    /**
     * A figure such as "25+ years of experience".
     */
    case Statistic = 'statistic';
}
