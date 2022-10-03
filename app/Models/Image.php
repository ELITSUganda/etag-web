<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'administrator_id',
        'src',
        'thumbnail',
        'parent_id',
        'size',
        'deleted_at',
        'type',
        'product_id',
    ];
}
