@php
    // import Utils class from models
    use App\Models\Utils;

    $owner = $animal->owner;
    $animal_owner = 'N/A';
    if ($owner != null) {
        $animal_owner = $owner->name;
    }
    $sub_county_text = 'N/A';
    if ($animal->sub_county != null) {
        $sub_county_text = $animal->sub_county->name_text;
    }
@endphp
<style>
    .start-on-top {
        margin-top: 0;
        vertical-align: top;
    }

    .title {
        font-weight: bold;
    }
</style>
<div class="container bg-white pl-5 pr-5 start-on-top pt-0"
    style="   box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.1);
    border: 8px solid #6B3B01!important;
    
    ">

    <div class="row p-0 ">
        <div class="col-12 p-0 pt-0 mt-3">
            <h1 class="text-left p-0 m-0">Animal's Profile</h1>
            <hr
                style="
                height: 5px;
                padding-top: 0px;
                margin-top: 5px;
                background-color: #6B3B01;
            ">
        </div>
    </div>
    <div class="row start-on-top">
        <div class="col-md-3 p-0">
            {{-- photo --}}
            <img src="https://cdn.forumcomm.com/dims4/default/bbafa1f/2147483647/strip/true/crop/1728x1152+0+733/resize/840x560!/quality/90/?url=https%3A%2F%2Fforum-communications-production-web.s3.us-west-2.amazonaws.com%2Fbrightspot%2Fdc%2Fe0%2F4666514e4071af3c7e3d5ab76aca%2Fhereford-cow-pasture.jpg"
                alt="Animal" class="w-100">
        </div>
        <div class="col-md-5 pt-0 mt-0 start-on-top">
            <h2 class="p-0 m-0">BIO Data</h1>
                @include('components.text-detail', ['t' => 'E-ID', 'v' => $animal->e_id])
                @include('components.text-detail', ['t' => 'V-ID', 'v' => $animal->v_id])
                @include('components.text-detail', ['t' => 'species', 'v' => $animal->type])
                @include('components.text-detail', ['t' => 'sex', 'v' => $animal->sex])
                @include('components.text-detail', ['t' => 'breed', 'v' => $animal->breed])
                @include('components.text-detail', ['t' => 'color', 'v' => $animal->color])
                @include('components.text-detail', ['t' => 'Animal Name', 'v' => $animal->details])
                @include('components.text-detail', [
                    't' => 'Date of birth',
                    'v' => Utils::my_date($animal->dob),
                ])
                @include('components.text-detail', [
                    't' => 'Date registered',
                    'v' => Utils::my_date($animal->created_at),
                ])
                @include('components.text-detail', [
                    't' => 'Last Updated',
                    'v' => Utils::my_date($animal->updated_at),
                ])
                @include('components.text-detail', [
                    't' => 'LHC',
                    'v' => $animal->lhc,
                ])@include('components.text-detail', [
                    't' => 'LHC Address',
                    'v' => $sub_county_text,
                ])
                @include('components.text-detail', [
                    't' => 'Animal Owner',
                    'v' => $animal_owner,
                ])
                @include('components.text-detail', [
                    't' => 'Has been vaccinated against FMD',
                    'v' => $animal->has_fmd ? 'Yes' : 'No',
                ])
                @include('components.text-detail', [
                    't' => 'FMD Date',
                    'v' => Utils::my_date($animal->fmd_date),
                ])
                {{-- animal weight --}}
                @include('components.text-detail', [
                    't' => 'Weight',
                    'v' => $animal->weight == 0 || $animal->weight == null ? 'N/A' : $animal->weight . ' KG',
                ])
                {{-- animal stage --}}
                @include('components.text-detail', [
                    't' => 'Stage',
                    'v' => strlen($animal->stage) == 0 || $animal->stage == null ? 'N/A' : $animal->stage,
                ])
                {{-- average_milk production --}}
                @include('components.text-detail', [
                    't' => 'Average Milk Production',
                    'v' =>
                        $animal->average_milk_production == 0 || $animal->average_milk_production == null
                            ? 'N/A'
                            : $animal->average_milk_production . ' Litres',
                ])
                {{-- was_purchases --}}
                @include('components.text-detail', [
                    't' => 'Was Purchased',
                    'v' => $animal->was_purchased ? 'Yes' : 'No',
                ])
                {{-- purchase_date --}}
                @include('components.text-detail', [
                    't' => 'Purchase Date',
                    'v' =>
                        $animal->purchase_date == null || strlen($animal->purchase_date) < 3
                            ? 'N/A'
                            : Utils::my_date($animal->purchase_date),
                ])

                {{-- purchase_from --}}
                @include('components.text-detail', [
                    't' => 'Purchased From',
                    'v' =>
                        $animal->purchase_from == null || strlen($animal->purchase_from) < 3
                            ? 'N/A'
                            : $animal->purchase_from,
                ])
                {{-- purchase_price in ugx --}}
                @include('components.text-detail', [
                    't' => 'Purchase Price',
                    'v' =>
                        $animal->purchase_price == 0 || $animal->purchase_price == null
                            ? 'N/A'
                            : 'UGX ' . number_format($animal->purchase_price),
                ])
                {{-- current_price in ugx --}}
                @include('components.text-detail', [
                    't' => 'Current Price',
                    'v' =>
                        $animal->current_price == 0 || $animal->current_price == null
                            ? 'N/A'
                            : 'UGX ' . number_format($animal->current_price),
                ])

                {{-- weight_at_birth --}}
                @include('components.text-detail', [
                    't' => 'Weight at Birth',
                    'v' => $animal->weight_at_birth == 0 || $animal->weight_at_birth == null ? 'N/A' : $animal->weight_at_birth . ' KG',
                ])

                {{-- conception --}}
                @include('components.text-detail', [
                    't' => 'Conception',
                    'v' => $animal->conception == null || strlen($animal->conception) < 2 ? 'N/A' : $animal->conception,
                ])

                {{-- genetic_donor --}}


        </div>

        {{-- 		
genetic_donor	
group_id	
comments	
local_id	
registered_by_id		
registered_id	
weight_change	
has_produced_before	
age_at_first_calving	
weight_at_first_calving	
has_been_inseminated	
age_at_first_insemination	
weight_at_first_insemination	
inter_calving_interval	
calf_mortality_rate	
weight_gain_per_day	
number_of_isms_per_conception	
is_a_calf	
is_weaned_off	
wean_off_weight	
wean_off_age	
last_profile_update_date	
profile_updated	
birth_position	
age	

--}}

        <div class="col-md-4  pt-0 mt-0 start-on-top">
            <h2 class="pt-0 mt-0 start-on-top">Animal Location</h1>
                <p>Animal District: Kampala</p>
                <p>Animal Sub County: Makindye</p>
                <p>Animal Parish: Kibuye</p>
                <p>Animal Village: Kibuye</p>
                <p>Animal Address: Kibuye, Makindye, Kampala</p>
        </div>
    </div>

    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsa maiores ut non necessitatibus officia. Consequatur
        mollitia deserunt, labore voluptatibus, optio dolore sunt, deleniti ipsa illo cumque dolor hic. Sapiente, quasi!
    </p>
</div>
