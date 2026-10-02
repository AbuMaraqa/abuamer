<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['eyebrow', 'title', 'text', 'button_label', 'button_url'])]
class SlideTranslation extends Model
{
    public $timestamps = false;
}
