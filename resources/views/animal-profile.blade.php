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
                    'v' =>
                        $animal->weight_at_birth == 0 || $animal->weight_at_birth == null
                            ? 'N/A'
                            : $animal->weight_at_birth . ' KG',
                ])

                {{-- conception --}}
                @include('components.text-detail', [
                    't' => 'Conception',
                    'v' =>
                        $animal->conception == null || strlen($animal->conception) < 2
                            ? 'N/A'
                            : $animal->conception,
                ])

                {{-- genetic_donor --}}
                @include('components.text-detail', [
                    't' => 'Genetic Donor',
                    'v' =>
                        $animal->genetic_donor == null || strlen($animal->genetic_donor) < 2
                            ? 'N/A'
                            : $animal->genetic_donor,
                ])

                {{-- group_text --}}
                @include('components.text-detail', [
                    't' => 'Group',
                    'v' =>
                        $animal->group_text == null || strlen($animal->group_text) < 2
                            ? 'N/A'
                            : $animal->group_text,
                ])

                {{-- is_pregnant --}}
                @include('components.text-detail', [
                    't' => 'Is Pregnant',
                    'v' => $animal->is_pregnant ? 'Yes' : 'No',
                ])

                {{-- weight_change weigt gain/loss --}}
                @include('components.text-detail', [
                    't' => 'Weight Gain/Loss',
                    'v' => $animal->weight_change == null ? 'N/A' : $animal->weight_change . ' KG',
                ])

                {{-- weight_at_last_calving --}}
                @include('components.text-detail', [
                    't' => 'Weight at Last Calving',
                    'v' =>
                        $animal->weight_at_last_calving == 0 || $animal->weight_at_last_calving == null
                            ? 'N/A'
                            : $animal->weight_at_last_calving . ' KG',
                ])

                {{-- has_produced_before --}}
                @include('components.text-detail', [
                    't' => 'Has Produced Before',
                    'v' => $animal->has_produced_before ? 'Yes' : 'No',
                ])

                {{-- number_of_calves --}}
                @include('components.text-detail', [
                    't' => 'Number of Calves',
                    'v' =>
                        $animal->number_of_calves == 0 || $animal->number_of_calves == null
                            ? 'N/A'
                            : $animal->number_of_calves,
                ])

                {{-- age_at_first_calving --}}
                @include('components.text-detail', [
                    't' => 'Age at First Calving',
                    'v' =>
                        $animal->age_at_first_calving == 0 || $animal->age_at_first_calving == null
                            ? 'N/A'
                            : $animal->age_at_first_calving,
                ])

                {{-- weight_at_first_calving --}}
                @include('components.text-detail', [
                    't' => 'Weight at First Calving',
                    'v' =>
                        $animal->weight_at_first_calving == 0 || $animal->weight_at_first_calving == null
                            ? 'N/A'
                            : $animal->weight_at_first_calving . ' KG',
                ])

                {{-- has_been_inseminated --}}
                @include('components.text-detail', [
                    't' => 'Has Been Inseminated',
                    'v' => $animal->has_been_inseminated ? 'Yes' : 'No',
                ])

                {{-- age_at_first_insemination --}}
                @include('components.text-detail', [
                    't' => 'Age at First Insemination',
                    'v' =>
                        $animal->age_at_first_insemination == 0 || $animal->age_at_first_insemination == null
                            ? 'N/A'
                            : $animal->age_at_first_insemination,
                ])

                {{-- weight_at_first_insemination --}}
                @include('components.text-detail', [
                    't' => 'Weight at First Insemination',
                    'v' =>
                        $animal->weight_at_first_insemination == 0 ||
                        $animal->weight_at_first_insemination == null
                            ? 'N/A'
                            : $animal->weight_at_first_insemination . ' KG',
                ])

                {{-- inter_calving_interval --}}
                @include('components.text-detail', [
                    't' => 'Inter Calving Interval',
                    'v' =>
                        $animal->inter_calving_interval == 0 || $animal->inter_calving_interval == null
                            ? 'N/A'
                            : $animal->inter_calving_interval . ' Days',
                ])

                {{-- calf_mortality_rate --}}
                @include('components.text-detail', [
                    't' => 'Calf Mortality Rate',
                    'v' =>
                        $animal->calf_mortality_rate == 0 || $animal->calf_mortality_rate == null
                            ? 'N/A'
                            : $animal->calf_mortality_rate . ' %',
                ])

                {{-- weight_gain_per_day --}}
                @include('components.text-detail', [
                    't' => 'Weight Gain Per Day',
                    'v' =>
                        $animal->weight_gain_per_day == 0 || $animal->weight_gain_per_day == null
                            ? 'N/A'
                            : $animal->weight_gain_per_day . ' KG',
                ])

                {{-- number_of_isms_per_conception --}}
                @include('components.text-detail', [
                    't' => 'Number of ISMs Per Conception',
                    'v' =>
                        $animal->number_of_isms_per_conception == 0 ||
                        $animal->number_of_isms_per_conception == null
                            ? 'N/A'
                            : $animal->number_of_isms_per_conception,
                ])

                {{-- is_a_calf --}}
                @include('components.text-detail', [
                    't' => 'Is a Calf',
                    'v' => $animal->is_a_calf ? 'Yes' : 'No',
                ])

                {{-- is_weaned_off --}}
                @include('components.text-detail', [
                    't' => 'Is Weaned Off',
                    'v' => $animal->is_weaned_off ? 'Yes' : 'No',
                ])

                {{-- birth_position --}}
                @include('components.text-detail', [
                    't' => 'Birth Position',
                    'v' =>
                        $animal->birth_position == 0 || $animal->birth_position == null
                            ? 'N/A'
                            : $animal->birth_position,
                ])

                {{-- birth_weight --}}


        </div>


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
