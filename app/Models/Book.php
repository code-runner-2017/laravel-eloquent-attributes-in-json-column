<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends MetaModel
{
    protected array $metaDeny = ['id', 'created_at', 'updated_at', 'title', 'description'];
}
