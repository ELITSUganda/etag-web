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

    public static function boot()
    {
        parent::boot();

        self::deleting(function ($m) {
            
        });
 
        self::created(function ($m) {
            $m->create_thumbail();   
        });
    }


    public function create_thumbail(){
        $src = $this->src; 
        $source = Utils::docs_root() ."/storage/images/".$this->src; 
        if(!file_exists($source)){ 
            $this->delete();
            echo "DNE => <code>$src</code> <hr>";
            return;
        } 
        $target = Utils::docs_root() ."/storage/images/thumb_".$this->src;
        
        Utils::create_thumbail([
            'source' => $source,
            'target' => $target
        ]); 

        if(file_exists($target)){
           $this->thumbnail = "thumb_".$this->src;
           $this->save();
         } 
        $size_2  = file_size( filesize($target));
        $size_1  = file_size(filesize($source));

        echo  "$size_1 <====> $size_2<br>";
        $link_1 = "/storage/images/".$this->src; 
        $link_2 = "/storage/images/thumb_".$this->src; 
        echo "<img src=\"$link_1\" width=\"30%\" >";
        echo "<img src=\"$link_2\" width=\"30%\" ><hr>";
       
    }
}
