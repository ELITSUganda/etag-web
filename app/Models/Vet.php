<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vet extends Model
{
    use HasFactory;

    public function getBusinessSubcountyTextAttribute($x)
    {
        $s = Location::find($this->business_subcounty_id);
        if ($s != null) {
            return $s->name_text;
        }
        return '';
    }

    public function getServicesTextAttribute($x)
    {

        $text = "";
        foreach (VetHasService::where([
            'vet_id' =>  $this->id,
        ])->get() as $key => $value) {
            if ($value->cat != null) {
                $text .= $value->cat->service_name . ", ";
            } else {
                $text .= $value->vet_service_category_id;
            }
        }
        return $text;
    }

    public function getServicesIdsAttribute($x)
    {

        $ids = [];
        foreach (VetHasService::where([
            'vet_id' =>  $this->id,
        ])->get() as $key => $value) {
            $ids[] = $value->vet_service_category_id;
        }
        return json_encode($ids);
    }

    protected $appends = ['business_subcounty_text', 'services_text','services_ids'];
}
