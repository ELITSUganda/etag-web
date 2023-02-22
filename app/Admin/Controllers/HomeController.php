<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\BatchSession;
use App\Models\DrugCategory;
use App\Models\DrugForSale;
use App\Models\Event;
use App\Models\Farm;
use App\Models\FormDrugSeller;
use App\Models\Image;
use App\Models\Movement;
use App\Models\MyFaker;
use App\Models\Utils;
use App\Models\VetServiceCategory;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Box;



class HomeController extends Controller
{


    public function become_farmer(Content $content)
    {

        AdminRoleUser::where([
            'role_id' => 12,
            'user_id' => Admin::user()->id,
        ])->delete();


        AdminRoleUser::where([
            'role_id' => 3,
            'user_id' => Admin::user()->id,
        ])->delete();

        $role = new AdminRoleUser();
        $role->role_id = 3;
        $role->user_id = Admin::user()->id;
        $role->save();
        return redirect(admin_url('/'));
    }
    public function index(Content $content)
    {


        $added = 0;
        foreach (DrugForSale::all() as $key => $v) {
            if ($v->images->count() > 3) {
                continue;
            }
            
            for ($i=0; $i < 4; $i++) { 
                $img = new Image();
                $img->type = 'DrugForSale';
                $img->parent_endpoint = 'DrugForSale';
                $img->parent_id = $v->id;
                $img->product_id = $v->id;
                $img->src = rand(1,31).".jpg";
                $img->administrator_id = $v->administrator_id;
                //$img->thumbnail = $img->src;
                $img->note = $v->name;
                $img->size = 1; 
                $img->save();
                
            } 
            $added++; 
        }

        dd("added ==>{$added} <====");

        /*  $txt =
        'acepromazine - sedative, tranquilizer, and antiemetic
        albendazole - antihelminthic
        alprazolam - benzodiazepine used as an anxiolytic and tranquilizer
        altrenogest - used to synchronizes estrus
        amantadine - analgesic for chronic pain
        aminophylline - bronchodilator
        amitraz - antiparasitic
        amitriptyline - tricyclic antidepressant used to treat separation anxiety, excessive grooming dogs and cats
        amlodipine - calcium channel blocker used to decrease blood pressure
        amoxicillin - antibacterial
        apomorphine - emetic (used to induce vomiting)
        artificial tears - lubricant eye drops used as a tear supplement
        atenolol - treats cardiac arrhythmias, hypertension, and diabetes plus other cardiovascular disorders
        atipamezole - α2-adrenergic antagonist used to reverse the sedative and analgesic effects of alpha-2 adrenergic receptor agonists
        carprofen - COX-2 selective NSAID used to relieve pain and inflammation in dogs and cats
        cephalexin - antibiotic, particularly useful for susceptible Staphylococcus infections
        cefovecin - cephalosporin-class antibiotic used to treat skin infections in dogs and cats
        cefpodoxime - antibiotic
        ceftiofur - cephalosporin antibiotic
        chloral hydrate/magnesium sulfate/pentobarbital - combination anesthetic agent
        chloramphenicol - antibacterial used to treat anaerobic bacterial infections, both Gram-positive and -negative
        cimetidine - H2 antagonist used to reduce gastric acid production
        ciprofloxacin - antibiotic of quinolone group
        clamoxyquine - antiparasitic to treat salmonids for infection with the myxozoan parasite, Myxobolus cerebralis
        clavamox - antibiotic, used to treat skin and other infections
        clavaseptin - antibiotic
        clavulanic acid - adjunct to penicillin-derived antibiotics used to overcome resistance in bacteria that secrete beta-lactamase
        clenbuterol - decongestant and bronchodilator used for the treatment of recurrent airway obstruction in horses
        clindamycin - antibiotic with particular use in dental infections with effects against most aerobic Gram-positive cocci
        clomipramine - primarily used in dogs to treat behavioral problems
        cyproheptadine - used as an appetite stimulant in cats and dogs
        deracoxib - (NSAID) nonsteroidal anti-inflammatory drug
        dexamethasone - anti-inflammatory steroid
        diazepam - benzodiazepine used to treat status epilepticus, also used as a preanaesthetic and a sedative
        dichlorophene - fungicide, germicide, and antimicrobial agent, also used for the removal of parasites
        diphenhydramine - histamine blocker
        doxycycline - antibiotic, also used to treat Lyme disease
        marbofloxacin - antibiotic
        maropitant - antiemetic
        mavacoxib - nonsteroidal anti-inflammatory drug (NSAID)
        medetomidine - surgical anesthetic and analgesic
        meloxicam - nonsteroidal anti-inflammatory drug (NSAID)
        metacam - used to reduce inflammation and pain
        methimazole - used in treatment of hyperthyroidism
        methocarbamol - muscle relaxant used to reduce muscle spasms associated with inflammation, injury, intervertebral disc disease, and certain toxicities
        metoclopramide - potent antiemetic, secondarily as a prokinetic
        metronidazole - antibiotic against anaerobic bacteria
        milbemycin oxime - broad spectrum antiparasitic used as an anthelmintic, insecticide and miticide
        mirtazapine - antiemetic and appetite stimulant in cats and dogs
        mitratapide - used to help weight loss in dogs
        morphine - pure mu agonist/opioid analgesic used as a premedication
        moxifloxacin - antibiotic
        neomycin - antibacterial
        nimuselide - nonsteroidal anti-inflammatory drug (NSAID)
        nitarsone - feed additive used in poultry to increase weight gain, improve feed efficiency, and prevent histomoniasis (blackhead disease)
        nitenpyram - insecticide
        nitroscanate - anthelmintic used to treat roundworms, hookworms and tapeworms
        nitroxynil - anthelmintic for fasciola and liver fluke infestations
        nystatin - antifungal
        oclacitinib - antipruritic
        ofloxacin - fluoroquinolone antibiotic
        omeprazole - used for treatment and prevention of gastric ulcers in horses
        oxibendazole - anthelmintic
        oxymorphone - analgesic
        oxytetracycline - antibiotic
        Onsior in cats - used to treat pain and inflammation
        pentobarbital - humane euthanasia of animals not to be used for food
        pentoxyfylline - xanthine derivative used in as an antiinflammatory drug and in the prevention of endotoxemia
        pergolide - dopamine receptor agonist used for the treatment of pituitary pars intermedia dysfunction in horses
        phenobarbital - anti-convulsant used for seizures
        phenylbutazone - nonsteroidal anti-inflammatory drug (NSAID)
        phenylpropanolamine - controls urinary incontinence in dogs
        phenytoin/pentobarbital - animal euthanasia product containing phenytoin and pentobarbital
        pimobendan - phosphodiesterase 3 inhibitor used to manage heart failure in dogs
        pirlimycin - antimicrobial
        ponazuril - anticoccidial
        praziquantel - treatment of infestations of the tapeworms Dipylidium caninum, Taenia pisiformis, Echinococcus granulosus
        prazosin - sympatholytic used in hypertension and abnormal muscle contractions
        prednisolone - glucocorticoid (steroid) used in the management of inflammation and auto-immune disease, primarily in cats
        prednisone - glucocorticoid (steroid) used in the management of inflammation and auto immune disease
        pregabalin - neuropathic pain reliever and anti-convulsant
        propofol - short acting intravenous drug used to induce anesthesia
        pyrantel - effective against ascarids, hookworms and stomach worms
        rafoxanide - parasiticide
        rifampin - anti-microbial primarily used in conjunction with other erythromycin in the treatment of Rhodococcus equi infections in foals
        robenacoxib - nonsteroidal anti-inflammatory drug (NSAID)
        roxarsone - arsenical used as a coccidiostat and for increased weight gain
        selamectin - antiparasitic treating fleas, roundworms, ear mites, heartworm, and hookworms
        silver sulfadiazine - antibacterial
        streptomycin - antibiotic used in large animals
        sucralfate - treats gastric ulcers
        sulfasalazine - anti-inflammatory and antirheumatic
        Telazol - intravenous drug used to induce anesthesia; combination of tiletamine and zolazepam
        tepoxalin - nonsteroidal anti-inflammatory drug (NSAID)
        theophylline - for bronchospasm and cardiogenic edema
        thiostrepton - antibiotic
        thiabendazole - antiparasitic
        tolfenamic acid — nonsteroidal anti-inflammatory drug (NSAID)
        tramadol - analgesic
        triamcinolone acetonide - corticosteroid
        trimethoprim — used widely for bacterial infections, is in the family of sulfa drugs
        trimethoprim/sulfadoxine — antibacterial containing trimethoprim and sulfadoxine
        trilostane - for canine Cushing\'s (hyperadrenocorticism) syndrome
        tylosin - antibiotic
        ';
        $recs = preg_split('/\r\n|\n\r|\r|\n/', $txt);


        $manufacturer = [
            'AAH (subsidiary of Celesio; 1923)',
            'Abbott (1888)',
            'AbbVie (2013)',
            'Acadia (1993)',
            'Acorda (1995)',
            'Adcock Ingram (1890)',
            ',Advanced Chemical Industries (conglomerate which includes a pharmaceutical subsidiary; 1968- )',
            'Advanz (1963)',
            ',Advaxis (2006)',
            'ACG Group (1961)',
            'Ajanta (1973)',
            'Alcon (former Novartis subsidiary; 2019)'
        ];
        $batch_number = [
            'UG-120XM1029112',
            'UG-120XM1029134',
            'UG-320XM1029134',
            'UG-3110XM1029134',
            'UG-6652320XM10PC',
            'UG-192717BBBY132',
        ];
        $cats = [];
        $selling_price = [12000, 10000, 13000, 5000, 12000, 4000, 25000, 7000];
        foreach (DrugCategory::all() as $key => $value) {
            $cats[] = $value->id;
        }
        $iii =1;

        foreach ($recs as $name) {
            $name = trim($name);
            if (strlen($name) < 2) {
                continue;
            }
            $drug = DrugForSale::where([
                'name' => $name
            ])->first();

            if ($drug != null) {
                continue;
            }
            $iii++;

            shuffle($cats);
            shuffle($manufacturer);
            shuffle($selling_price);
            shuffle($batch_number);
            $drug = new DrugForSale();
            $drug->administrator_id = 1;
            $u = Administrator::find($drug->administrator_id);
            $drug->administrator_id = $cats[2];
            $drug->drug_category_id = $cats[2];
            $drug->vet_id =  $u->vet_profile->id;
            $drug->name =  $name;
            $drug->manufacturer =  $manufacturer[0];
            $drug->batch_number =  $batch_number[0];
            $drug->ingredients =  'Item 1, Item 2, Item 3, Item 4, Item 5, Item 6';
            $d = new Carbon();
            $d->addDays(rand(-121, 360));
            $drug->expiry_date = $d;
            $drug->original_quantity = rand(100, 10000);
            $drug->current_quantity = $drug->original_quantity - rand(100, 1000);
            $drug->selling_price = $selling_price[0];
            $drug->image = '';
            $drug->details = 'Amitraz (development code BTS27419) is a non-systemic acaricide and insecticide[1] and has also been described as a scabicide.
            It was first synthesized by the Boots Co. in England in 1969.
            Amitraz has been found to have an insect repellent effect, works as an insecticide and also as a pesticide synergist.
            Its effectiveness is traced back on alpha-adrenergic agonist activity, interaction with octopamine receptors of the central nervous system and inhibition of monoamine oxidases and prostaglandin synthesis.[4] Therefore, it leads to overexcitation and consequently paralysis and death in insects. Because amitraz is less harmful to mammals, amitraz is among many other purposes best known as insecticide against mite- or tick-infestation of dogs.
            It is also widely used in the beekeeping industry as a control for the Varroa destructor mite, although there are recent reports of resistance (driven by overuse and off label use)';
 
            $drug->save();

            echo "$name<br>";
        }
        die(" added $iii");
        dd($txt); */

        /*   $cats = [
            "Hardware disease treatment",
            "Health and travel certificates",
            "Lameness evaluation and treatment",
            "Lumpy jaw and woody tongue",
            "Navel infections",
            "Oxygen supplementation",
            "Pneumonia treatments",
            "Pregnancy evaluations",
            "Routine newborn care",
            "Vaccinations",
            "Vaginal and rectal prolapse",
            "Wound care",
            "Blood donation"
        ];

        foreach ( $cats  as $key => $c) {
            $cat = new  VetServiceCategory();
            $cat->service_name = trim($c);
            $cat->service_description = trim($c);
            $cat->save();
        } */

        /*
  $min = Carbon::parse('02/01/2023');  
        $max = Carbon::parse('02/02/2023');  
  
        192
        807
        548.0 
        807.0

        128
        
       "02 Feb, 2023 - 12:02 am" 
       "03 Feb, 2023 - 12:02 am"
   
 
 

        $milk = Event::whereBetween('created_at', [$min, $max])
        ->where([
            'type' => 'Milking', 
        ])
        ->sum('milk');

        $animals = [];
        $i = 0;
        foreach (Event::whereBetween('created_at', [$min, $max])
        ->where([
            'type' => 'Milking', 
        ])->get() as $key => $ev) {
            $i++;
            if(!in_array($ev->animal_id,$animals)){
                $animals[] = $ev->animal_id;
            }else{
                $ev->delete();
            } 

        }
    
        echo "ALL animals ==> ".$i."<===<br>";
        echo "UNIQUE animals ==> ".count($animals)."<===<br>";
        echo "milk ==> {$milk}<===<br>";


        die(".");

       
        foreach (BatchSession::all() as $b) {
            Event::where([
                'session_id' => $b->id,
            ])
            ->where('id','>','3831')
            ->delete(); 
            $b->delete();
            
            # code...
        }
         \OneSignal::setParam('android_channel_id', 'f3469729-c2b4-4fce-89da-78550d5a2dd1')->sendNotificationToExternalUser(
            "Some Message",
            '777',
            $url = null,
            $data = null,
            $buttons = null,
            $schedule = null
        );
 */

        //MyFaker::makeEvents(3000);
        //die("as");  
        //MyFaker::makeAnimals(1000);

        $u = Admin::user();
        if ($u->isRole('farmer')) {

            $content
                ->title('U-LITS - Dashboard')
                ->description('Hello ' . $u->name . "!");

            $content->row(function (Row $row) {
                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::farmerSummary());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::farmerEvents());
                });
                $row->column(5, function (Column $column) {
                    $column->append(Dashboard::milkCollection());
                });
            });

            return $content;
        }

        if ($u->isRole('farmer')) {
            if (count($u->farms) < 1) {
                $content->row(function ($row) {
                    $row->column(
                        3,
                        view('admin.dashboard.component-wizard', [
                            'step' => "1",
                            'text' => "CREATE YOUR FIRST FARM",
                            'link' => admin_url('farms/create'),
                        ])
                    );
                });
                return $content;
            }
        }

        $f = FormDrugSeller::where('applicant_id', Admin::user()->id)->first();
        if ($f != null) {
            if ($f->status != 1) {
                return redirect(admin_url('form-drug-sellers'));
            }
        }

        if (Admin::user()->isRole('default')) {
            $content->row(function ($row) {
                $user =  Admin::user();
                $row->column(6, new Box(
                    null,
                    view('admin.dashboard.default-user')
                ));
            });
        }

        if (
            Admin::user()->isRole('administrator') ||
            Admin::user()->isRole('admin')
        ) {
            Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');
            $content->title('Main Dashboard');

            $content->row(function ($row) {
                $box = new Box('Livestock Species', view('admin.dashboard.chart-animal-types'));
                $box->removable();
                $box->collapsable();
                $box->style('success');
                $box->solid();
                $row->column(6, $box);

                $box = new Box('Events', view('admin.dashboard.chart-animal-status'));
                $box->removable();
                $box->collapsable();
                $box->style('success');
                $box->solid();
                $row->column(6, $box);
            });
            $content->row(function ($row) {
                $admins = Administrator::all();

                $farmers_count = 0;
                $trader_count = 0;
                $administrator_count = 0;
                $veterinary_count = 0;
                $trader_count = 0;
                $slaughter_count = 0;
                $livestock_count = 0;
                foreach ($admins as $key => $_ad) {
                    if ($_ad->isRole('farmer')) {
                        $farmers_count++;
                    }
                    if ($_ad->isRole('trader')) {
                        $trader_count++;
                    }
                    if ($_ad->isRole('administrator')) {
                        $administrator_count++;
                    }
                    if ($_ad->isRole('veterinary')) {
                        $veterinary_count++;
                    }
                    if ($_ad->isRole('trader')) {
                        $trader_count++;
                    }
                    if ($_ad->isRole('slaughter')) {
                        $slaughter_count++;
                    }
                    if ($_ad->isRole('livestock-officer	')) {
                        $livestock_count++;
                    }
                }
                $row->column(4, new InfoBox(
                    ''
                        . "{$administrator_count} Admins, "
                        . "{$trader_count} veterinarians, "
                        . "{$trader_count} traders, "
                        . "{$slaughter_count} Slaughter houses, "
                        . "{$livestock_count} Livestock officers, "
                        . "{$farmers_count} Farmers.",
                    'All users',
                    'green',
                    admin_url('/auth/users'),
                    Administrator::count() . " - Users"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Farm::where('farm_type', 'Dairy')->count()) . "Dairy,"
                        . number_format(Farm::where('farm_type', 'Beef')->count()) . " Beef,"
                        . number_format(Farm::where('farm_type', 'Mixed')->count()) . " Mixed, ",
                    'All farms',
                    'green',
                    admin_url('/farms'),
                    Farm::count() . " - Holdings"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Farm::where('farm_type', 'Dairy')->count()) . " Dairy, "
                        . number_format(Farm::where('farm_type', 'Beef')->count()) . " Beef, "
                        . number_format(Farm::where('farm_type', 'Mixed')->count()) . " Mixed, ",
                    'All Livestock',
                    'green',
                    admin_url('/animals'),
                    number_format(Animal::count()) . " - Livestock"
                ));
            });
        }


        //Farmer
        if (Admin::user()->isRole('farmer')) {
            Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');


            $content->title('Main Dashboard');




            $content->row(function ($row) {
                $user =  Admin::user();
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Farm::where('farm_type', 'Dairy')->where('administrator_id', $user->id)->count()) . " Dairy, "
                        . number_format(Farm::where('farm_type', 'Beef')->where('administrator_id', $user->id)->count()) . " Beef, "
                        . number_format(Farm::where('farm_type', 'Mixed')->where('administrator_id', $user->id)->count()) . " Mixed, ",
                    'All farms',
                    'green',
                    admin_url('/farms'),
                    Farm::where('administrator_id', $user->id)->count() . " - Farms"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Animal::where('type', 'Cattle')->where('administrator_id', $user->id)->count()) . " Cattle, "
                        . number_format(Animal::where('type', 'Goat')->where('administrator_id', $user->id)->count()) . " Goat, "
                        . number_format(Animal::where('type', 'Sheep')->where('administrator_id', $user->id)->count()) . " Sheep, ",
                    'All animals',
                    'green',
                    admin_url('/animals'),
                    number_format(Animal::where('administrator_id', $user->id)->count()) . " - Animals"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Movement::where('status', 'Pending')->where('administrator_id', $user->id)->count()) . " Pending, "
                        . number_format(Movement::where('status', 'Halted')->where('administrator_id', $user->id)->count()) . " Halted, "
                        . number_format(Movement::where('status', 'Rejected')->where('administrator_id', $user->id)->count()) . " Rejected ",
                    'All animals',
                    'green',
                    admin_url('/animals'),
                    number_format(Movement::where('administrator_id', $user->id)->count()) . " - Movement permits"
                ));
            });
        }


        if (Admin::user()->isRole('trader')) {

            Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');
            $content->title('Main Dashboard');


            $content->row(function ($row) {
                $user =  Admin::user();
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Animal::where('type', 'Cattle')->where('trader', $user->id)->count()) . " Cattle, "
                        . number_format(Animal::where('type', 'Goat')->where('trader', $user->id)->count()) . " Goats, "
                        . number_format(Animal::where('type', 'Sheep')->where('trader', $user->id)->count()) . " Sheep, ",
                    'All animals',
                    'green',
                    admin_url('/sales'),
                    number_format(Animal::where('trader', $user->id)->count()) . " - Animals in stock"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Movement::where('status', 'Pending')->where('administrator_id', $user->id)->count()) . " Pending, "
                        . number_format(Movement::where('status', 'Halted')->where('administrator_id', $user->id)->count()) . " Halted, "
                        . number_format(Movement::where('status', 'Rejected')->where('administrator_id', $user->id)->count()) . " Rejected ",
                    'All animals',
                    'green',
                    admin_url('/movements'),
                    number_format(Movement::where('administrator_id', $user->id)->count()) . " - Movement permits"
                ));
            });
        }
        /*
	
id
created_at
updated_at

vehicle
reason
status
trader_nin
trader_name
trader_phone Ascending 1
transporter_name
transporter_nin
transporter_Phone
district_from
sub_county_from
village_from
district_to
sub_county_to
village_to
transportation_route
permit_Number
valid_from_Date
valid_to_Date
status_comment
destination
destination_slaughter_house
details
destination_farm
is_paid
paid_id
paid_method
*/

        return $content;
    }
}
