<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends MetaModel
{
    protected array $metaAllow = ['this_is_a_custom_meta', 'this_is_another_meta'];
}
