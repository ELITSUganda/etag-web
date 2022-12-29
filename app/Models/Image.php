<?php

namespace App\Models;

use Dflydev\DotAccessData\Util;
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

    public function create_thumbail(){
        $src = $this->src; 
        $source = Utils::docs_root() ."/storage/images/".$this->src; 
        if(!file_exists($source)){ 
            echo "DNE => <code>$src</code> <hr>";
        } 
         
        //Utils::create_thumbail(); 
    }
}
