<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugForSale extends Model
{
    use HasFactory;

    public function getImagesAttribute()
    {
        return  json_encode(Image::where([
            'type' => 'DrugForSale',
            'parent_id' => $this->id,
        ])->get());
    }

    public function getVetTextAttribute()
    {
        $cat = Vet::find($this->vet_id);
        if ($cat == null) {
            return "-";
        }
        return $cat->business_name;
    }

    public function getBusinessLogoAttribute()
    {
        $cat = Vet::find($this->vet_id);
        if ($cat == null) {
            return "-";
        }
        return $cat->business_logo;
    }

    public function getDrugCategoryTextAttribute()
    {
        $cat = DrugCategory::find($this->drug_category_id);
        if ($cat == null) {
            return "-";
        }
        return $cat->name;
    }
    protected $appends = ['images', 'drug_category_text', 'vet_text', 'business_logo'];
}
