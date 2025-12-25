<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaughterDistributionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'slaughterhouse_id',
        'created_by_id',
        'source_type',
        'source_id',
        'source_name',
        'source_address',
        'source_phone',
        'receiver_type',
        'receiver_id',
        'receiver_name',
        'receiver_address',
        'receiver_phone',
        'lhc',
        'v_id',
        'e_id',
        'animal_owner_id',
        'bar_code',
        'qr_code',
        'post_fat',
        'post_grade',
        'post_animal',
        'post_age',
        'original_weight',
        'current_weight',
        'price',
        'slaughter_date',
        'cut_type',
    ];

    protected $appends = [
        'source_text',
        'receiver_text',
        'v_text',
        'e_text',
        'animal_text',
        'slaughterhouse_text',
        'created_by_text',
        'animal_owner_text',
    ];

    // Relationships
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function slaughterRecord()
    {
        return $this->belongsTo(SlaughterRecord::class, 'source_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'created_by_id');
    }

    // Appends for mobile app compatibility
    public function getSourceTextAttribute()
    {
        if ($this->source_id && $this->source_name) {
            return "{$this->source_name} (ID: {$this->source_id})";
        }
        return $this->source_name ?? '';
    }

    public function getReceiverTextAttribute()
    {
        if ($this->receiver_id && $this->receiver_name) {
            return "{$this->receiver_name} (ID: {$this->receiver_id})";
        }
        return $this->receiver_name ?? '';
    }

    public function getVTextAttribute()
    {
        return $this->v_id ?? '';
    }

    public function getETextAttribute()
    {
        return $this->e_id ?? '';
    }

    public function getAnimalTextAttribute()
    {
        if ($this->animal) {
            return "{$this->animal->v_id} - {$this->animal->e_id}";
        }
        return '';
    }

    public function getSlaughterhouseTextAttribute()
    {
        return $this->slaughterhouse_id ? "Slaughterhouse #{$this->slaughterhouse_id}" : '';
    }

    public function getCreatedByTextAttribute()
    {
        if ($this->createdBy) {
            return $this->createdBy->name;
        }
        return '';
    }

    public function getAnimalOwnerTextAttribute()
    {
        if ($this->animal_owner_id) {
            $owner = \Encore\Admin\Auth\Database\Administrator::find($this->animal_owner_id);
            return $owner ? $owner->name : "Owner #{$this->animal_owner_id}";
        }
        return '';
    }

    /**
     * Check if this record is a Quarter (has address containing "Fore-1/4" or "Hind-1/4")
     */
    public function isQuarter()
    {
        $address = strtolower($this->source_address ?? '');
        return str_contains($address, 'fore-1/4') || str_contains($address, 'hind-1/4');
    }

    /**
     * Check if this record is a Cut (has cut_type = "Prime" or "Offal")
     */
    public function isCut()
    {
        return in_array($this->cut_type, ['Prime', 'Offal']);
    }

    /**
     * Get all quarters for a specific carcass (source_id)
     */
    public static function getQuartersForCarcass($carcassId)
    {
        return self::where('source_id', $carcassId)
            ->where(function ($query) {
                $query->where('source_address', 'like', '%Fore-1/4%')
                    ->orWhere('source_address', 'like', '%Hind-1/4%');
            })
            ->get();
    }

    /**
     * Get all cuts for a specific carcass (source_id)
     */
    public static function getCutsForCarcass($carcassId, $cutType = null)
    {
        $query = self::where('source_id', $carcassId)
            ->whereIn('cut_type', ['Prime', 'Offal']);
        
        if ($cutType) {
            $query->where('cut_type', $cutType);
        }
        
        return $query->get();
    }
}
