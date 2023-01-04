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

            $src = Utils::docs_root() . "/storage/images/" . $m->src;

            if ($m->thumbnail != null) {
                if (strlen($m->thumbnail) > 2) {
                    $thumb = Utils::docs_root() . "/storage/images/" . $m->thumbnail;
                }
            }
            if (!isset($thumb)) {
                $thumb =  Utils::docs_root() . "/storage/images/thumb_" . $m->src;
            }

            if (file_exists($src)) {
                unlink($src);
            }
            if (file_exists($thumb)) {
                unlink($thumb);
            }
        });

        self::created(function ($m) {
            $m->create_thumbail();
        });
    }

    public function getSrcAttribute($src)
    {

        $source = Utils::docs_root() . "/storage/images/" . $src;
        if (!file_exists($source)) {
            return 'logo.png';
        }
        return $source;
    }
    public function getThumbnailAttribute($src)
    {

        $source = Utils::docs_root() . "/storage/images/" . $src;
        if (!file_exists($source)) {
            return 'logo.png';
        }
        return $source;
    }

    public function create_thumbail()
    {
        $src = $this->src;
        $source = Utils::docs_root() . "/storage/images/" . $this->src;
        if (!file_exists($source)) {
            $this->delete();
            return;
        }
        $target = Utils::docs_root() . "/storage/images/thumb_" . $this->src;

        Utils::create_thumbail([
            'source' => $source,
            'target' => $target
        ]);

        if (file_exists($target)) {
            $this->thumbnail = "thumb_" . $this->src;
            $this->save();
        }
    }
}
