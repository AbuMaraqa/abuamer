<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'tagline', 'hero_title', 'hero_subtitle', 'introduction', 'story', 'vision', 'mission', 'cta_title', 'cta_text'])]
class CompanyTranslation extends Model
{
    public $timestamps = false;
}
