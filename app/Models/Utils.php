<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Model;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zebra_Image;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class Utils extends Model
{

    public static function img($photo, $local)
    {

        $default_img_path = public_path('logo.jpg');
        $default_img_url = url('logo.jpg');

        if ($photo == null || strlen($photo) < 3) {
            if ($local) {
                return $default_img_path;
            } else {
                return $default_img_url;
            }
        }
        //split $photo with /
        $exp = explode('/', $photo);
        if (count($exp) > 1) {
            $photo = $exp[count($exp) - 1];
        }

        $local_path = public_path('storage/images/' . $photo);
        if (!file_exists($local_path)) {
            if ($local) {
                return $default_img_path;
            } else {
                return $default_img_url;
            }
        }
        if ($local) {
            return $local_path;
        } else {
            return url('storage/images/' . $photo);
        }
    }
    public static function get_cattle_age($dob)
    {
        //return age in months (in years if > 12)
        $born = null;
        try {
            $born = Carbon::parse($dob);
        } catch (\Throwable $e) {
            return "N/A";
        }
        if ($born == null) {
            return "N/A";
        }
        $now = Carbon::now();
        $diff = $born->diffInMonths($now);
        if ($diff < 12) {
            return $diff . " Months";
        } else {
            $years = floor($diff / 12);
            $months = $diff % 12;
            return $years . " Years and " . $months . " Months.";
        }
    }
    public static function get_cattle_stage($dob, $sex)
    {
        $sex = strtolower($sex);
        $born = null;
        try {
            $born = Carbon::parse($dob);
        } catch (\Throwable $e) {
            return "Unknown";
        }
        if ($born == null) {
            return "Unknown";
        }
        $now = Carbon::now();
        $diff = $born->diffInMonths($now);
        if ($diff < 6) {
            return "Calf";
        } else if ($diff < 12) {
            if ($sex == 'male') {
                return "Bull Calf";
            } else {
                return "Heifer";
            }
            return "Weaned Calf/Heifer or Bull Calf";
        } else if ($diff < 18) {
            return "Yearling";
        } else if ($diff < 24) {
            //Breeding Heifer
            return "Breeding";
        } else if ($diff < 72) {
            if ($sex == 'male') {
                return "Bull";
            } else {
                return "Cow";
            }
            return "Mature Cow/Bull";
        } else {
            if ($sex == 'male') {
                return "Bull";
            } else {
                return "Cow";
            }
        }
    }

    public static function get_unique_text()
    {
        //get uniqte text
        $section_0 = uniqid();
        $section_1 = time();
        $section_2 = rand(1000000, 99999999);
        $section_3 = rand(1000000, 99999999);
        $unique_text = $section_0 . '-' . $section_1 . '-' . $section_2 . '-' . $section_3;
        return $unique_text;
    }
    public static function import_farms()
    {
        return;
        $exel_path = public_path('storage/files/farms.xlsx');
        //check if file exists
        if (!file_exists($exel_path)) {
            die("File not found.");
        }
        //load excel file
        $data = Excel::toArray(null, $exel_path);
        if ($data == null) {
            die("No data found.");
        }
        //loop through data
        /* excel data     
    0 => "Farmer is New?"
    1 => "Farmer name"
    2 => "Farmer/N.O.K National Identification Number (NIN)"
    3 => "Farmer/N.O.K phone number"
    4 => "Has farm had FMD vacination before"
    5 => "Date of last vaccination"
    6 => "_GPS coordinates_latitude"
    7 => "_GPS coordinates_longitude"
    8 => "Farm name"
    9 => "Sub county name"
    10 => "Village name"
    11 => "Farm space (in Ha)"
    12 => "Farm type"
    13 => "Number of cattle"
    14 => "Number of goats"
    15 => "Number of sheep"
    16 => "Number of pigs"
    17 => "Responsible officer name"
    18 => "Contact of responsible officer"
    
    */
        /* 

nin	
gender	
phone_number_2	
details	
temp_id	
sub_county_id	
dvo	
scvo	
	
status	
first_name	
last_name	
milk_price	
picked_roles	
district_id	
business_name	
business_license_number	
business_license_issue_authority	
business_license_issue_date	
business_license_validity	
business_address	
business_phone_number	
business_whatsapp	
business_email	
business_logo	
business_cover_photo	
business_cover_details	
vet_service	
request_status	
farm_id	
language	
    */  //set max execution time
        set_time_limit(0);
        //set max memory
        ini_set('memory_limit', '1024M');
        $isFirst = true;
        foreach ($data[0] as $key => $val) {
            if ($isFirst) {
                $isFirst = false;
                continue;
            }
            $phone = Utils::prepare_phone_number($val[3]);
            if (!Utils::phone_number_is_valid($phone)) {
                $phone = $val[3];
            }
            $nin = $val[2];
            if (strlen($phone) > 3) {
                $farmer = User::where([
                    'phone_number' => $phone,
                ])->first();
                if ($farmer == null) {
                    $farmer = User::where([
                        'username' => $phone,
                    ])->first();
                }
            }

            if ($farmer == null) {
                $farmer = User::where([
                    'nin' => $nin,
                ])->first();
            }

            if ($farmer == null) {
                $farmer = new User();
                $farmer->name = $val[1];
                $names = explode(' ', $val[1]);
                if (isset($names[1])) {
                    $farmer->last_name = $names[1];
                }
                if (isset($names[1])) {
                    $farmer->first_name = $names[0];
                }
                $farmer->phone_number = $phone;
                if (strlen($phone) > 3) {
                    $farmer->username = $phone;
                } else {
                    $farmer->username = $nin;
                }
                $farmer->nin = $nin;
                $farmer->user_type = 'Farmer';
                $farmer->business_cover_details = 'NEW';
                $farmer->address = $val[10];
                $farmer->password = password_hash('4321', PASSWORD_DEFAULT);
                $farmer->save();
                $farmer = User::find($farmer->id);
            }

            $subcount_text = $val[9];

            if ($subcount_text == 'Busaana') {
                $subcount_text = 'Busana';
            }

            $subcount = Location::where([
                'name' => $subcount_text
            ])->first();
            $farm = Farm::where([
                'farm_owner_nin' => $farmer->nin,
                'village' => $val[10],
            ])->first();
            if ($farm != null) {
                continue;
            }
            $farm = Farm::where([
                'administrator_id' => $farmer->id,
                'village' => $val[10],
            ])->first();
            if ($farm != null) {
                continue;
            }


            $farm = new Farm();
            $farm->administrator_id = $farmer->id;

            if ($subcount != null) {
                $farm->district_id = $subcount->parent;
                $farm->sub_county_id = $subcount->id;
            } else {
                $farm->district_id = 1002007;
                $farm->sub_county_id = 1002007;
            }
            if ($subcount->type == 'District') {
                $firstSub = Location::where([
                    'parent' => $subcount->id
                ])->orderBy('id', 'asc')->first();
                if ($firstSub != null) {
                    $farm->sub_county_id = $firstSub->id;
                }
            }
            $farm->village = $val[10];
            $farm->farm_owner_name = $val[1];
            $farm->farm_owner_nin = $val[2];
            $farm->farm_owner_phone_number = $val[3];
            $farm->pigs_count = $val[16];
            $farm->sheep_count = $val[15];
            $farm->goats_count = $val[14];
            $farm->cattle_count = $val[13];
            $farm->farm_type = $val[12];
            $farm->latitude = $val[6];
            $farm->longitude = $val[7];
            $farm->has_fmd = $val[4];
            $farm->dfm = $val[5];
            $farm->village = $val[10];
            $farm->duplicate_results = 'NEW';
            $farm->animals_count = $val[13] + $val[14] + $val[15] + $val[16];
            $farm->save();
            echo $key . ". sAVED FARM: $val[8] belonging to $val[1] <br>";
        }
        /* 
			
	
	
	
 	
	
name	
	
	

farm_owner_is_new	
is_processed	
	

	
local_id	
registered_id	
duplicate_checked	
duplicate_results	
	 
*/
        die("done.");
    }
    public static function process_duplicate_farms()
    {
        $SQL = 'SELECT holding_code, COUNT(holding_code) as count FROM `farms` GROUP BY holding_code HAVING count > 1';
        $farms = DB::select($SQL);
        foreach ($farms as $key => $farm) {
            $dups = Farm::where('holding_code', $farm->holding_code)->get();
            foreach ($dups as $dup) {
                $sub = Location::where([
                    'id' => $dup->sub_county_id,
                    'type' => 'Sub-County',
                ])->first();
                if ($sub == null) {
                    $dup->sub_county_id = 1002007;
                    $sub = Location::where([
                        'id' => $dup->sub_county_id,
                        'type' => 'Sub-County',
                    ])->first();
                }
                $new_lhc = Utils::get_next_lhc($sub);
                $dup->holding_code = $new_lhc;
                $dup->save();
            }
        }
    }

    public static function get_next_lhc($sub)
    {
        //check if is string or int
        if (is_string($sub) || is_int($sub)) {
            $sub = Location::find([
                'id' => $sub,
                'type' => 'Sub-County',
            ])->first();
        }
        if ($sub == null) {
            throw new Exception("Sub-county not found.");
        }
        if (!$sub->isSubCounty()) {
            throw new Exception("Invalid sub-county.");
        }
        $last_farm =
            Farm::where(['sub_county_id' => $sub->id])
            ->orderBy('id', 'desc')->first();

        $num = 0;

        if ($last_farm != null) {
            $exp = explode('-', $last_farm->holding_code);
            if (is_array($exp)) {
                if (isset($exp[count($exp) - 1])) {
                    $num = (int)($exp[count($exp) - 1]);
                }
            }
        }

        $num++;
        $holding_code = $sub->code . "-" . $num;
        $dup_farm = Farm::where([
            'holding_code' => $holding_code
        ])->first();
        if ($dup_farm != null) {
            $holding_code = $sub->code . "-" . ($num + 2);
        }

        if ($dup_farm != null) {
            $dup_farm = Farm::where([
                'holding_code' => $holding_code
            ])->first();
            $holding_code = $sub->code . "-" . ($num + 3);
        }
        if ($dup_farm != null) {
            $dup_farm = Farm::where([
                'holding_code' => $holding_code
            ])->first();
            $holding_code = $sub->code . "-" . ($num + 4);
        }
        if ($dup_farm != null) {
            $dup_farm = Farm::where([
                'holding_code' => $holding_code
            ])->first();
            $holding_code = $sub->code . "-" . ($num + 5);
        }
        if ($dup_farm != null) {
            $dup_farm = Farm::where([
                'holding_code' => $holding_code
            ])->first();
            $holding_code = $sub->code . "-" . ($num + 6);
        }
        if ($dup_farm != null) {
            $dup_farm = Farm::where([
                'holding_code' => $holding_code
            ])->first();
            $holding_code = $sub->code . "-" . ($num + 7);
        }
        if ($dup_farm != null) {
            $dup_farm = Farm::where([
                'holding_code' => $holding_code
            ])->first();
            $holding_code = $sub->code . "-" . ($num + 8);
        }
        if ($dup_farm != null) {
            $dup_farm = Farm::where([
                'holding_code' => $holding_code
            ])->first();
            $holding_code = $sub->code . "-" . ($num + 9);
        }
        if ($dup_farm != null) {
            $dup_farm = Farm::where([
                'holding_code' => $holding_code
            ])->first();
            $holding_code = $sub->code . "-" . ($num + 10);
        }


        $dup_farm = Farm::where([
            'holding_code' => $holding_code
        ])->first();
        if ($dup_farm != null) {

            for ($i = 0; $i < 10000; $i++) {
                $dup_farm = Farm::where([
                    'holding_code' => $holding_code
                ])->first();
                if ($dup_farm == null) {
                    break;
                }
                $num++;
                $holding_code = $sub->code . "-" . $num;
            }
        }

        return $holding_code;
    }
    public static function check_duplicates()
    {
        //change time
        set_time_limit(0);
        //change memory
        ini_set('memory_limit', '1024M');
        $farms = Farm::where('duplicate_checked', '!=', 'Yes')->get();
        foreach ($farms as $key => $f1) {
            $dups = Farm::where('holding_code', $f1->holding_code)->get();
            if ($dups->count() < 1) {
                continue;
            }
            foreach ($dups as $key2 => $f2) {
                $f1->duplicate_checked  = 'Yes';
                if ($f2->id == $f1->id) {
                    $f1->save();
                    continue;
                }
                $sub = Location::find($f2->id);
                if ($sub == null) {
                    $f2->sub_county_id = 1002007;
                    $sub = Location::find($f2->sub_county_id);
                    $f2->duplicate_results = "Sub county not found.";
                }
                $next_lhc = self::get_next_lhc($sub);
                $f2->holding_code = $next_lhc;
                $f2->duplicate_checked = 'Yes';
                $f2->save();
            }
        }


        $recs = FarmVaccinationRecord::where('duplicate_checked', '!=', 'Yes')->get();

        foreach ($recs as $key => $val) {
            $val2 = FarmVaccinationRecord::where([
                'farm_id' => $val->farm_id,
                'vaccine_main_stock_id' => $val->vaccine_main_stock_id,
                'district_vaccine_stock_id' => $val->district_vaccine_stock_id,
                'vaccination_batch_number' => $val->vaccination_batch_number,
                'number_of_doses' => $val->number_of_doses,
                'number_of_animals_vaccinated' => $val->number_of_animals_vaccinated,
                'lhc' => $val->lhc,
            ])->get();

            foreach ($val2 as $key => $v) {
                if ($val->id == $v->id) {
                    $val->duplicate_checked = 'Yes';
                    $val->save();
                    continue;
                }
                $v->do_reverse();
            }
        }
    }

    public static function generate_qrcode($data)
    {
        $obj = new DNS2D();
        $multiplier = 2;
        $path = "";
        try {
            $multiplier = 3;
            $path = $obj->getBarcodePNGPath($data, 'QRCODE', 3 * $multiplier, 3 * $multiplier, array(0, 0, 0), true);
        } catch (Exception $e) {
            throw $e;
        }
        return $path;
    }

    public static function generate_barcode($data)
    {
        $obj = new DNS1D();
        $multiplier = 2;
        $path = "";
        try {
            $path = $obj->getBarcodePNGPath($data, 'C128', 3 * $multiplier, 66 * $multiplier, array(0, 0, 0), true);
        } catch (Exception $e) {
            throw $e;
        }
        return $path;
    }

    public static function get_finance_report($u)
    {
        /* 
        $cats = FinanceCategory::where([
            'administrator_id' => $u->id,
        ])->get();

        for ($i = 0; $i < 50; $i++) {
            $t = new Transaction();
            $t->finance_category_id = $cats[rand(0, count($cats) - 1)]->id;
            $t->amount = rand(1000, 100000);
            $t->is_income = rand(0, 1);
            $t->description = "Test Transaction $i";
            $transaction_date = Carbon::now()->subDays(rand(0, 365));
            $t->transaction_date = $transaction_date;
            $t->administrator_id = $u->id;
            $t->district_id = 1;
            $t->sub_county_id = 1;
            $t->farm_id = 1;
            $t->save();
        } */


        $data = [];
        $data['total_income'] = 0;
        $data['total_expense'] = 0;
        $data['total_balance'] = 0;
        $data['total_income'] = Transaction::where([
            'is_income' => 1,
            'administrator_id' => $u->id,
        ])->sum('amount');
        $data['total_expense'] = Transaction::where([
            'is_income' => 0,
            'administrator_id' => $u->id,
        ])->sum('amount');

        $start_date = date('2023-01-01');
        $end_date = now();
        $months = Carbon::parse($start_date)->monthsUntil($end_date);
        $monthly_datas = [];
        $data['total_balance'] = $data['total_income'] + $data['total_expense'];



        foreach ($months as $key => $month) {
            $monthly_data['income'] = Transaction::where([
                'is_income' => 1,
                'administrator_id' => $u->id,
            ])->whereMonth('transaction_date', $month->month)->sum('amount');
            $monthly_data['expense'] = Transaction::where([
                'is_income' => 0,
                'administrator_id' => $u->id,
            ])->whereMonth('transaction_date', $month->month)->sum('amount');
            $monthly_data['balance'] = $monthly_data['income'] + $monthly_data['expense'];
            $monthly_data['month'] = $month->format('F');
            $monthly_data['year'] = $month->format('Y');

            //check if is current month
            if ($month->format('Y-m') == now()->format('Y-m')) {
                $data['current_month'] = $monthly_data;
                continue;
            }
            $monthly_datas[] = $monthly_data;
        }

        //reverse months $monthly_datas
        $monthly_datas = array_reverse($monthly_datas);

        $data['monthly_datas'] = $monthly_datas;
        return $data;
    }

    public static function send_message($phone_number, $message)
    {
        return '';
        if (!Utils::validateUgandanPhoneNumber($phone_number)) {
            return "$phone_number is not a valid phone number.";
        }

        $url = "https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?username=mubaraka&passwd=muh1nd0@2023";
        $url .= "&msg=" . trim($message);
        $url .= "&numbers=" . $phone_number;
        $my_response = "";
        try {
            $result = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    /* 'content' => json_encode($m), */
                ],
            ]));
            if (str_contains($result, 'Send ok')) {
                $my_response = "";
            } else {
                $my_response = "Failed to send sms because " . ((string)$result);
            }
        } catch (\Throwable $th) {
            $my_response = $th->getMessage();
        }
        return $my_response;
    }

    public static function validateUgandanPhoneNumber($phoneNumber)
    {
        $num = Utils::prepareUgandanPhoneNumber($phoneNumber);

        if ($num == '') {
            return false;
        }
        if (strlen($num) < 13) {
            return false;
        }
        if (strlen($num) > 15) {
            return false;
        }
        return true;
    }


    public static function prepareUgandanPhoneNumber($phoneNumber)
    {
        $phoneNumber = trim($phoneNumber);
        $phoneNumber = str_replace(' ', '', $phoneNumber);
        if (substr($phoneNumber, 0, 1) == '0') {
            $phoneNumber = substr($phoneNumber, 1);
        } else if (substr($phoneNumber, 0, 3) == '256') {
            $phoneNumber = substr($phoneNumber, 3);
        } else if (substr($phoneNumber, 0, 4) == '+256') {
            $phoneNumber = substr($phoneNumber, 4);
        }
        if (strlen($phoneNumber) < 8) {
            return '';
        }
        $phoneNumber = '+256' . $phoneNumber;
        return $phoneNumber;
        // Remove any non-numeric characters from the phone number
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if the phone number starts with "07", "256", or "+256"
        if (preg_match('/^(07|256|\+256)([1-9]\d+)$/', $phoneNumber, $matches)) {
            // Extract the numeric part
            $numericPart = $matches[2];

            // Standardize the phone number by adding "7" after "0" and "+256" at the beginning
            $standardizedNumber = '+256' . '0' . $numericPart;

            return $standardizedNumber;
        } else {
            // If the phone number does not match the expected format, return it as is
            return $phoneNumber;
        }
    }


    public static function quantity_convertor_2($qty, $stock)
    {
        $val = $qty / $stock->drug_packaging_unit_quantity;
        $unit = "";
        if ($stock->drug_state == 'Solid') {
            $unit = "Tablets";
        } else {
            $unit = "Bottoles";
        }
        return number_format($val) . " " . $unit;
    }



    public static function quantity_convertor($qty, $type)
    {
        if ($type == 'Solid') {
            $val = $qty;
            if ($qty > 1000000) {
                $val = $qty / 1000000;
                return number_format($val) . "kg";
            } else if ($qty > 1000) {
                $val = $qty / 1000;
                return number_format($val) . "g";
            }
            return number_format($val) . "mg";
        } else  if ($type == 'Liquid') {
            $val = $qty;
            if ($qty > 1000) {
                $val = $qty / 1000;
                return number_format($val) . "L";
            }
            return number_format($val) . "ml";
        }
    }

    public static function systemBoot($u)
    {
        return;
        //get administartors who don't have any group
        $admins = Administrator::whereNotIn('id', function ($query) {
            $query->select('administrator_id')
                ->from('groups');
        })->get();

        try {
            // Utils::check_duplicates();
        } catch (\Throwable $th) {
            //throw $th;
        }

        //loop and create a group for each admin, make is_main_group = Yes
        foreach ($admins as $key => $admin) {
            $group = new Group();
            $group->administrator_id = $admin->id;
            $group->name = "Main Group";
            $group->is_main_group = 'Yes';
            $group->save();
        }
        //get all animals whose group_id is null
        $animals = Animal::whereNull('group_id')->get();
        //loop and create a group for each animal, make is_main_group = No
        foreach ($animals as $key => $animal) {
            //get main group of this animal administrator_id
            $group = Group::where([
                'administrator_id' => $animal->administrator_id,
                'is_main_group' => 'Yes',
            ])->first();
            if ($group == null) {
                continue;
            }
            $animal->group_id = $group->id;
            $animal->save();
        }

        $arcs = ArchivedAnimal::whereNull('administrator_id')->get();
        foreach ($arcs as $key => $val) {
            $farm = Farm::where([
                'holding_code' => $val->lhc
            ])->first();
            if ($farm == null) {
                continue;
            }
            $val->administrator_id = $farm->administrator_id;
            $val->save();
        }


        if ($u == null) {
            return;
        }
        //Utils::make_profile_pics($u);
        //Utils::transferPhotos($u);
        //Utils::prepareThumbnails();
        Utils::prepareOrders();
        Utils::prepareAverageMilk();
    }



    public static function create_thumbnail($file_path)
    {
        if (!file_exists($file_path)) {
            return null;
        }

        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        if ($ext == null) {
            return null;
        }
        $ext = strtolower($ext);

        if (!in_array($ext, [
            'jpg',
            'jpeg',
            'png',
            'gif',
        ])) {
            return null;
        }
        $file_name_1 = basename($file_path);
        $file_name_2 = 'temp_' . $file_name_1;


        $image = new Zebra_Image();
        $image->handle_exif_orientation_tag = false;
        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->jpeg_quality = 15;

        $file_path_2 = str_replace($file_name_1, $file_name_2, $file_path);


        $image->auto_handle_exif_orientation = true;
        $image->source_path =  $file_path;
        $image->target_path =  $file_path_2;
        if (!$image->resize(0, 0, ZEBRA_IMAGE_CROP_CENTER)) {
            return null;
        }
        return $file_path_2;
    }


    public static function makeSlug($s)
    {
        $s = Str::slug($s, '-');
        $count = Product::where([
            'slug' => $s
        ])->count();
        if ($count > 0) {
            $s .= "-" . $count;
        }
        return $s;
    }

    public static function un_paid_order_sum($u)
    {
        $count = ProductOrder::where(
            'order_is_paid',
            '!=',
            1
        )->where([
            'customer_id' => $u->id
        ])
            ->sum('total_price');
        return $count;
    }

    public static function get_pending_orders($u)
    {
        $count = ProductOrder::where(
            'order_is_paid',
            '!=',
            1
        )->where([
            'customer_id' => $u->id
        ])
            ->sum('total_price');
        return $count;
    }

    public static function prepareThumbnails()
    {


        $imgs = Image::where([
            'thumbnail' => NULL,
        ])
            ->orderBy('id', 'Desc')
            ->get();
        if (count($imgs) < 1) {
            return;
        }


        $i = 1;
        foreach ($imgs as $key => $img) {
            $i++;
            echo "<h2>$i</h2>";
            $img->create_thumbail();
        }

        //average_milk

    }

    public static function make_profile_pics($u)
    {

        $x = 1;
        $link = $_SERVER['DOCUMENT_ROOT'] . '/storage/images/';

        $files = glob($link . '*JPG');
        foreach ($files as $file) {
            $source  = "$file";
            $e_id = $source;
            $src = str_replace($link, "", $e_id);

            if (!str_contains($src, '(m)')) {
                continue;
            }

            $e_id = str_replace('-(m).JPG', '', $src);

            $animal = Animal::where([
                'e_id' => $e_id
            ])->first();

            if ($animal == null) {
                die("Animal not found.");
            }

            $animal->photo =  'storage/images/' . $src;

            $animal->save();
            echo "<img src=\"$animal->photo\" >";


            echo $e_id . ' <hr>';
        }


        die("Romina");
    }


    public static function transferPhotos($u)
    {

        $x = 1;
        $link = $_SERVER['DOCUMENT_ROOT'] . '/storage/temp/';

        $files = glob($link . '*JPG');
        foreach ($files as $file) {
            $source  = "$file";
            $e_id = $source;
            $src = str_replace($link, "", $e_id);
            $e_id = str_replace($link, "", $e_id);
            $exp = explode(' ', $e_id);
            if (!isset($exp[1])) {
                continue;
            }
            $e_id = trim($exp[0]);
            if (strlen($e_id) < 3) {
                continue;
            }

            $animal = Animal::where([
                'e_id' => $e_id
            ])->first();

            if ($animal == null) {
                echo ("<h2>" . $e_id . " not found.</h2>");
                continue;
            }

            $target = $source;
            $target = str_replace('temp/', 'images/', $target);
            $target = str_replace(' ', '-', $target);
            $src = str_replace(" ", "-", $src);

            $img = Image::where([
                'src' => $src
            ])->first();

            if ($img != null) {
                echo ("image exists on DB <br>");
            } else {
                echo ("NEW image on DB <br>");
                $img = new Image();
                $img->administrator_id = $u->id;
                $img->src = $src;
                $img->thumbnail = null;
                $img->parent_id = $animal->id;
                $img->type = $animal->id;
                $img->type = 'animal';
                $img->note = 'Photo taken on: Sunday - December 18th, 2022';
                $img->parent_endpoint = 'Animal';
                $img->save();
            }

            if (file_exists($target)) {
                echo ("IMAGE exists in files");
            } else {
                echo ("NEW IMAGE in files");
            }

            rename($source, $target);

            echo '<hr>';
        }


        die("Romina");
    }

    public static function prepareOrders()
    {
        foreach (
            WholesaleOrder::where([
                'status' => 'Processing',
                'processed' => 'No',
            ])->get() as $key => $order
        ) {

            $status = $order->validate_order();
            if ($status != null) {
                continue;
            }
            try {
                WholesaleOrder::do_process_order($order);
                $order->processed = 'Yes';
                $order->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }
    public static function prepareAverageMilk()
    {

        $animals = Animal::where([
            'sex' => 'Female',
            'average_milk' => NULL,
        ])->get();
        if (count($animals) < 1) {
            return;
        }

        foreach ($animals as $key => $animal) {
            $animal->calculateAverageMilk();
        }


        //average_milk

    }

    public static function month($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('M - Y');
    }
    public static function my_day($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M');
    }






    public static function my_time_ago($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->diffForHumans();
    }


    public static function my_date_1($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('D - d M');
    }

    public static function my_date($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y');
    }
    public static function my_date_2($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y - D');
    }

    public static function my_date_time($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y - h:i a');
    }

    public static function to_date_time($raw)
    {
        $t = Carbon::parse($raw);
        if ($t == null) {
            return  "-";
        }
        $my_t = $t->toDateString();

        return $my_t . " " . $t->toTimeString();
    }
    public static function number_format($num, $unit)
    {
        $num = (int)($num);
        $resp = number_format($num);
        if ($num < 2) {
            $resp .= " " . $unit;
        } else {
            $resp .= " " . Str::plural($unit);
        }
        return $resp;
    }


    public static function get_object($class, $id)
    {
        $data = $class::find($id);
        if ($data != null) {
            return $data;
        }
        return new $class();
    }


    public static function phone_number_is_valid($phone_number)
    {
        $phone_number = Utils::prepare_phone_number($phone_number);
        if (substr($phone_number, 0, 4) != "+256") {
            return false;
        }

        if (strlen($phone_number) != 13) {
            return false;
        }

        return true;
    }
    public static function prepare_phone_number($phone_number)
    {
        $original = $phone_number;
        //$phone_number = +256775679505';
        //0783204665
        if (strlen($phone_number) > 10) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = substr($phone_number, 3, strlen($phone_number));
        } else {
            if (substr($phone_number, 0, 1) == "0") {
                $phone_number = substr($phone_number, 1, strlen($phone_number));
            }
        }
        if (strlen($phone_number) != 9) {
            return $original;
        }
        return "+256" . $phone_number;
    }


    public static function display_alert_message()
    {
        Utils::start_session();
        if (isset($_SESSION['alerts'])) {
            if ($_SESSION['alerts'] != null) {
                foreach ($_SESSION['alerts'] as $key => $v) {
                    if (isset($v['type']) && isset($v['msg'])) {
                        echo view('components.alert', $v);
                    }
                }
            }
            $_SESSION['alerts'] == null;
            unset($_SESSION['alerts']);
        }
    }

    public static function alert_message($type, $msg)
    {
        Utils::start_session();
        $alert['type'] = $type;
        $alert['msg'] = $msg;
        $_SESSION['alerts'][] = $alert;
    }

    public static function start_session()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    public static function get_file_url($name)
    {
        $url = url("/storage");
        if ($name == null) {
            $url .= '/default.png';
            return $url;
        }
        if ($name == null || (strlen($name) < 2)) {
        } else if (file_exists(public_path('storage/' . $name))) {
            $url .= "/" . $name;
        } else {
            $url .= '/default.png';
        }
        return $url;
    }

    public static function make_movement_qr($model)
    {
        $p_url = url("/print?id=" . $model->id);
        $data = $model->id;

        $data = "ULITS E-MOVEMENT PERMIT\n" .
            "Applicant: $model->trader_name\n" .
            "Transporter: $model->transporter_name\n" .
            "PERMIT No.: $model->permit_Number\n" .
            "PERMIT Status: $model->status\n" .
            "VERIFICATION URL: $p_url\n";
        return $data;

        Utils::make_qr([
            'file_name' => $model->id . ".png",
            'data' => $data,
        ]);
    }
    public static function make_qr($opts = [
        'file_name' => '1.png',
        'data' => 'Data',
    ])
    {
        $url = url('code_maker.php?f=png&s=qr&sf=20&ms=r&md=.8&d=' . urlencode($opts['data']));
        $data = file_get_contents($url);
        //$myfile = fopen("public/storage/codes/" . $opts['file_name'], "w");
        //fwrite($myfile, $data);
        //fclose($myfile);
    }

    public static function move_animal($transfer = [])
    {
        if (
            isset($transfer['animal_id']) &&
            isset($transfer['destination_farm_id'])
        ) {
            $animal = Animal::find($transfer['animal_id']);
            $farm = Farm::find($transfer['destination_farm_id']);
            if (
                $animal != null &&
                $farm != null
            ) {
                $animal->administrator_id = $farm->administrator_id;
                $animal->district_id = $farm->district_id;
                $animal->sub_county_id = $farm->sub_county_id;
                $animal->lhc = $farm->holding_code;
                $animal->farm_id = $farm->id;
                if ($animal->save()) {
                    $event = new Event();
                    $event->administrator_id = $animal->administrator_id;
                    $event->district_id = $animal->district_id;
                    $event->sub_county_id = $animal->sub_county_id;
                    $event->farm_id = $animal->farm_id;
                    $event->animal_id = $animal->id;
                    $event->type = "Moved";
                    $event->approved_by = Admin::user()->id;
                    $event->detail = "Animal moved to LHC " . $animal->lhc;
                    $event->disease_id = null;
                    $event->vaccine_id = null;
                    $event->medicine_id = null;
                    $event->save();
                }
            }
        }
    }

    public static function get_role($u = null)
    {
        if ($u == null) {
            return  "";
        }
        $roles = $u->roles;
        if (isset($roles[0])) {
            if (isset($roles[0]['slug'])) {
                return $roles[0]['slug'];
            }
        }
        return "";
    }
    public static function is_admin($request = null)
    {
        if ($request == null) {
            return false;
        }
        $header = (int)($request->header('user'));
        if ($header < 1) {
            return false;
        }
        $u = Administrator::find($header);
        if ($u == null) {
            return false;
        }

        $roles = $u->roles;
        if (isset($roles[0])) {
            if (isset($roles[0]['slug'])) {
                return $roles[0]['slug'];
            }
        }

        return "";

        if (!$u->isRole('veterinary')) {
            return  false;
        }

        if (!$u->isRole('veterinary')) {
            return  false;
        }

        if ($u->isRole('veterinary')) {
            return  true;
        }
        return  false;
    }

    public static function get_status($s = null)
    {
        if ($s == null) {
            return "";
        } else if ($s == "Pending") {
            return '<a href="badge badge-warning"></a>';
        }

        return "";
    }

    public static function get_drug_status($s = 0)
    {
        if ($s == 0) {
            return '<span class="badge badge-warning">pending</span>';
        } else if ($s == 1) {
            return '<span class="badge badge-success">approved</span>';
        } else {
            return '<span class="badge badge-warning">pending</span>';
        }
        return "";
    }

    public static function get_user_id($request = null)
    {
        if ($request == null) {
            return 0;
        }
        $header = (int)($request->header('user'));
        if ($header < 1) {
            $header = (int)($request->user);
        }

        if ($header < 1) {
            if (isset($request->user_id)) {
                $header = (int)($request->user_id);
            }
        }

        //temp_worker_id
        if ($header < 1) {
            return 0;
        }
        return $header;
    }

    public static function get_temp_worker_id($request = null)
    {
        $header = (int)($request->header('temp_worker_id'));
        if ($header < 1) {
            $header = (int)($request->user);
        }

        if ($header < 1) {
            if (isset($request->temp_worker_id)) {
                $header = (int)($request->temp_worker_id);
            }
        }
        if ($header > 0) {
            return $header;
        }

        if ($request == null) {
            return 0;
        }




        //temp_worker_id
        if ($header < 1) {
            return 0;
        }
        return $header;
    }

    public static function response($data = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        $resp['status'] = "1";
        $resp['message'] = "Success";
        $resp['data'] = null;
        if (isset($data['status'])) {
            $resp['status'] = $data['status'] . "";
        }
        if ($resp['status'] == '1' || $resp['status'] == 1) {
            $resp['code'] = "1";
        } else {
            $resp['code'] = "0";
        }

        if (isset($data['message'])) {
            $resp['message'] = $data['message'];
        }
        if (isset($data['data'])) {
            $resp['data'] = $data['data'];
        }
        return $resp;
    }

    public static function archive_animal($data = [])
    {
        if (!isset($data['animal_id'])) {
            return false;
        }
        $animal_id = (int)($data['animal_id']);

        if ($animal_id < 1) {
            return false;
        }
        $animal = Animal::find($animal_id);
        if ($animal == null) {
            return false;
        }

        $ArchivedAnimal = new ArchivedAnimal();
        $ArchivedAnimal->owner = "-";
        if (isset($data['event'])) {
            $ArchivedAnimal->last_event = $data['event'];
        }
        if (isset($data['details'])) {
            $ArchivedAnimal->details = $data['details'];
        }

        if (isset($data['reason'])) {
            $ArchivedAnimal->last_event = $data['reason'];
        }


        if (($animal->farm != null)) {
            if (($animal->farm->owner() != null)) {
                try {
                    $ArchivedAnimal->owner = $animal->farm->owner()->name;
                    $ArchivedAnimal->district = $animal->farm->district->name;
                    $ArchivedAnimal->sub_county = $animal->farm->sub_county->name;
                } catch (Exception $x) {

                    $ArchivedAnimal->owner = 'N/A';
                    $ArchivedAnimal->district = '1';
                    $ArchivedAnimal->sub_county = '1';
                }
            }
        }
        $ArchivedAnimal->type = $animal->type;
        $ArchivedAnimal->e_id = $animal->e_id;
        $ArchivedAnimal->v_id = $animal->v_id;
        $ArchivedAnimal->lhc = $animal->lhc;
        $ArchivedAnimal->breed = $animal->breed;
        $ArchivedAnimal->sex = $animal->sex;
        $ArchivedAnimal->dob = $animal->dob;
        $ArchivedAnimal->administrator_id = $animal->administrator_id;

        $ArchivedAnimal->events = json_encode($animal->events);
        if ($ArchivedAnimal->save()) {
            Event::where([
                'animal_id' => $animal_id
            ])->delete();
            $animal->delete();
            return true;
        }
        return true;
    }


    public static function create_thumbail($params = array())
    {

        ini_set('memory_limit', '-1');

        if (
            !isset($params['source']) ||
            !isset($params['target'])
        ) {
            return [];
        }



        if (!file_exists($params['source'])) {
            $img = url('assets/images/cow.jpeg');
            return $img;
        }


        $image = new Zebra_Image();

        $image->auto_handle_exif_orientation = true;
        $image->source_path = "" . $params['source'];
        $image->target_path = "" . $params['target'];


        if (isset($params['quality'])) {
            $image->jpeg_quality = $params['quality'];
        }

        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->handle_exif_orientation_tag = true;

        $img_size = getimagesize($image->source_path); // returns an array that is filled with info





        $image->jpeg_quality = 50;
        if (isset($params['quality'])) {
            $image->jpeg_quality = $params['quality'];
        } else {
            $image->jpeg_quality = Utils::get_jpeg_quality(filesize($image->source_path));
        }
        if (!$image->resize(0, 0, ZEBRA_IMAGE_CROP_CENTER)) {
            return $image->source_path;
        } else {
            return $image->target_path;
        }
    }

    public static function get_jpeg_quality($_size)
    {
        $size = ($_size / 1000000);

        $qt = 50;
        if ($size > 5) {
            $qt = 10;
        } else if ($size > 4) {
            $qt = 10;
        } else if ($size > 2) {
            $qt = 10;
        } else if ($size > 1) {
            $qt = 11;
        } else if ($size > 0.8) {
            $qt = 11;
        } else if ($size > .5) {
            $qt = 12;
        } else {
            $qt = 15;
        }

        return $qt;
    }

    public static function process_images_in_backround()
    {
        $url = url('api/process-pending-images');
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        try {
            $data =  file_get_contents($url, null, $ctx);
            return $data;
        } catch (Exception $x) {
            return "Failed $url";
        }
    }

    public static function process_images_in_foreround()
    {
        $imgs = Image::where([
            'thumbnail' => null
        ])->get();

        foreach ($imgs as $img) {
            $thumb = Utils::create_thumbail([
                'source' => 'public/storage/images/' . $img->src,
                'target' => 'public/storage/images/thumb_' . $img->src,
            ]);
            if ($thumb != null) {
                if (strlen($thumb) > 4) {
                    $img->thumbnail = $thumb;
                    $img->save();
                }
            }
        }
    }



    public static function upload_images_2($files, $is_single_file = false)
    {

        ini_set('memory_limit', '-1');
        if ($files == null || empty($files)) {
            return $is_single_file ? "" : [];
        }
        $uploaded_images = array();
        foreach ($files as $file) {

            if (
                isset($file['name']) &&
                isset($file['type']) &&
                isset($file['tmp_name']) &&
                isset($file['error']) &&
                isset($file['size'])
            ) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = time() . "-" . rand(100000, 1000000) . "." . $ext;
                $destination = Utils::docs_root() . '/storage/images/' . $file_name;

                $res = move_uploaded_file($file['tmp_name'], $destination);
                if (!$res) {
                    continue;
                }
                //$uploaded_images[] = $destination;
                $uploaded_images[] = $file_name;
            }
        }

        $single_file = "";
        if (isset($uploaded_images[0])) {
            $single_file = $uploaded_images[0];
        }


        return $is_single_file ? $single_file : $uploaded_images;
    }




    public static function upload_images_1($files, $is_single_file = false)
    {

        ini_set('memory_limit', '-1');
        if ($files == null || empty($files)) {
            return $is_single_file ? "" : [];
        }
        $uploaded_images = array();
        foreach ($files as $file) {

            if (
                isset($file['name']) &&
                isset($file['type']) &&
                isset($file['tmp_name']) &&
                isset($file['error']) &&
                isset($file['size'])
            ) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = time() . "-" . rand(100000, 1000000) . "." . $ext;
                $destination = 'public/storage/images/' . $file_name;

                $res = move_uploaded_file($file['tmp_name'], $destination);
                if (!$res) {
                    continue;
                }
                //$uploaded_images[] = $destination;
                $uploaded_images[] = $file_name;
            }
        }

        $single_file = "";
        if (isset($uploaded_images[0])) {
            $single_file = $uploaded_images[0];
        }


        return $is_single_file ? $single_file : $uploaded_images;
    }


    public static function docs_root()
    {
        return public_path();
        $r = $_SERVER['DOCUMENT_ROOT'] . "";

        if (!str_contains($r, 'home/')) {
            $r = str_replace('/public', "", $r);
            $r = str_replace('\public', "", $r);
        }

        $r = $r . "/public";

        /* 
         "/home/ulitscom_html/public/storage/images/956000011639246-(m).JPG
        
        public_html/public/storage/images
        */
        return $r;
    }


    public static function CreateNotification(
        $params = [
            'receiver_id' => null,
            'type' => null,
            'animal_id' => null,
            'event_id' => null,
            'animal_ids' => [],
            'event_ids' => [],
            'notification_id' => null,
            'notification_ids' => [],
            'session_id' => null,
            'session_ids' => [],
            'message' => 'Open U-LITS App fore more details.',
            'title' => 'U-LITS',
            'data' => [],
            'url' => null,
            'buttons' => null,
            'image' => 'logo.png',
        ],
    ) {

        if ($params['receiver_id'] == null) {
            throw new Exception("Receiver ID is required.");
        }
        if ($params['type'] == null) {
            throw new Exception("Type is required.");
        }
        if ($params['type'] == "") {
            throw new Exception("Type is required.");
        }
        if ($params['title'] == null) {
            throw new Exception("Title is required.");
        }
        if ($params['title'] == "") {
            throw new Exception("Title is required.");
        }
        if ($params['message'] == null) {
            throw new Exception("Message is required.");
        }
        $receiver = $params['receiver_id'];
        $msg = $params['message'];
        $noti = new NotificationModel();
        $noti->data = json_encode($params);
        $noti->title = $params['title'];

        if (isset($params['image'])) {
            $noti->image = $params['image'];
        }
        $noti->reciever_id = $receiver;
        $noti->status = 'NOT READ';
        $noti->type = $params['type'];
        $noti->message = $msg;
        if (isset($params['session_id'])) {
            $noti->session_id = $params['session_id'];
        }
        if (isset($params['session_ids'])) {
            $noti->session_ids = json_encode($params['session_ids']);
        }
        if (isset($params['url'])) {
            $noti->url = $params['url'];
        }
        if (isset($params['buttons'])) {
            $noti->buttons = json_encode($params['buttons']);
        }
        if (isset($params['event_id'])) {
            $noti->event_id = $params['event_id'];
        }
        if (isset($params['event_ids'])) {
            $noti->event_ids = json_encode($params['event_ids']);
        }
        if (isset($params['message'])) {
            $params['message'] = $params['message'];
        }
        if (isset($params['animal_id'])) {
            $noti->animal_id = $params['animal_id'];
        }

        if (isset($params['animal_ids'])) {
            $noti->animal_ids = json_encode($params['animal_ids']);
        }

        if (isset($data['type'])) {
            $noti->type = $data['type'];
        }
        try {
            $noti->save();
        } catch (\Throwable $th) {
            throw $th;
        }

        try {
            \OneSignal::addParams(
                [
                    'android_channel_id' => 'f3469729-c2b4-4fce-89da-78550d5a2dd1',
                    'large_icon' => 'https://u-lits.com/logo-1.png',
                    'small_icon' => 'logo_1',
                ]
            )
                ->sendNotificationToExternalUser(
                    $noti->message,
                    $noti->reciever_id . "",
                    $url = null,
                    $data = null,
                    $buttons = [],
                    $schedule = null,
                    $headings = $noti->title
                );
        } catch (\Throwable $th) {
            $noti->delete();
            throw $th;
        }


        return;
    }

    public static function sendNotification(
        $msg,
        $receiver,
        $headings = 'U-LITS',
        $data = [],
        $url = null,
        $buttons = null,
        $schedule = null,
    ) {

        $noti = new NotificationModel();
        $noti->title = $headings;
        $noti->message = $msg;
        $noti->data = '[]';
        $temp_data = [];
        $noti->image = 'logo.png';
        if ($data != null && is_array($data)) {
            try {
                foreach ($data as $key => $value) {
                    $animal = Animal::find((int)(($value)));
                    if ($animal == null) {
                        $arhive = ArchivedAnimal::get_animal((int)(($value)));
                        if ($arhive == null) {
                            continue;
                        }
                        $animal = $arhive;
                        $temp_data_item = [];
                        $temp_data_item['id'] = $value;
                        $temp_data_item['e_id'] = $animal->e_id;
                        $temp_data_item['photo'] = 'logo.png';
                        $temp_data_item['last_seen'] = $animal->updated_at;
                        $temp_data_item['type'] = $animal->type;
                        $temp_data[] = $temp_data_item;
                        continue;
                    }
                    if ($animal->photo == null) {
                        continue;
                    }
                    $temp_data_item = [];
                    $temp_data_item['id'] = $animal->id;
                    $temp_data_item['e_id'] = $animal->e_id;
                    $temp_data_item['photo'] = $animal->photo;
                    $temp_data_item['last_seen'] = $animal->updated_at;
                    $temp_data_item['type'] = $animal->type;
                    $temp_data[] = $temp_data_item;

                    //if $animal->photo does not contain logo.png
                    if (!str_contains($animal->photo, 'logo.png')) {
                        $noti->image = $animal->photo;
                    }
                }
            } catch (\Throwable $th) {
                $temp_data = [];
            }
        }

        $noti->data = json_encode($temp_data);

        $noti->reciever_id = $receiver;
        $noti->status = 'NOT READ';
        $noti->type = 'NOTIFICATION';


        if (isset($data['type'])) {
            $noti->type = $data['type'];
        }
        try {
            $noti->save();
            $data['id'] = $noti->id;
            $data['notification_id'] = $noti->id;
        } catch (\Throwable $th) {
            throw $th;
        }


        /* 

        $table->text('')->nullable();
        $table->string('')->nullable();
        */
        try {
            \OneSignal::addParams(
                [
                    'android_channel_id' => 'f3469729-c2b4-4fce-89da-78550d5a2dd1',
                    'large_icon' => 'https://u-lits.com/logo-1.png',
                    'small_icon' => 'logo_1',
                ]
            )
                ->sendNotificationToExternalUser(
                    $msg,
                    "$receiver",
                    $url = $url,
                    $data = $data,
                    $buttons = $buttons,
                    $schedule = $schedule,
                    $headings = $headings
                );
        } catch (\Throwable $th) {
            throw $th;
        }


        return;
    }
    public static function getTableColumns($obj)
    {
        $table = $obj->getTable();


        $cols = DB::getSchemaBuilder()->getColumnListing($table);
        if ($cols == null) {
            $cols = [];
        }
        if (!is_array($cols)) {
            $cols = [];
        }
        return  $cols;

        // OR

        return Schema::getColumnListing($table);
    }

    public static function is_local()
    {
        $url = $_SERVER['HTTP_HOST'];
        $segs = explode('/', strtolower($url));
        if (in_array('u-lits.com', $segs)) {
            return false;
        }
        return true;
    }
    public static function create_dummy_content()
    {
        $u = User::find(709);
        if ($u == null) {
            return;
        }
        $farm = Farm::find(128);
        if ($farm == null) {
            return;
        }

        $sex = [
            'Female',
            'Female',
            'Female',
            'Female',
            'Male',
        ];

        $farm_group = Group::where([
            'administrator_id' => $u->id
        ])
            ->latest()
            ->first();
        $latest_images = Image::latest()
            ->limit(400)
            ->get();
        $faker = \Faker\Factory::create();
        //set unlimited memory limit
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        for ($i = 1; $i <= 100; $i++) {
            $e_id = str_pad($i, 10, '0', STR_PAD_LEFT);
            $v_id = str_pad($i, 4, '0', STR_PAD_LEFT);

            // Check if e_id already exists
            if (Animal::where('e_id', $e_id)->exists()) {
                continue;
            }

            $animal = new Animal();
            $animal->administrator_id = 709;
            $animal->district_id = $faker->numberBetween(1, 100);
            $animal->sub_county_id = $faker->numberBetween(1, 100);
            $animal->parish_id = $faker->numberBetween(1, 100);
            $animal->status = 'Active';
            $animal->type = 'Cattle';
            $animal->e_id = $e_id;
            $animal->v_id = $v_id;
            $animal->lhc = $farm->holding_code;
            $animal->breed = 'Anlole';
            $animal->sex = $faker->randomElement($sex);
            $now = Carbon::now();
            $animal->dob = $now->subMonths(rand(5, 50))->format('Y-m-d');
            $animal->color = $faker->colorName;
            $animal->farm_id = 128;
            $animal->fmd = $faker->randomElement(['Yes', 'No']);
            // $animal->trader = $faker->name;
            $animal->destination = $faker->address;
            // $animal->destination_slaughter_house = $faker->address;
            // $animal->destination_farm = $faker->address;
            $animal->details = $faker->text;
            $animal->for_sale = $faker->boolean;
            $animal->price = rand(100, 5000) . '000';
            $animal->weight = rand(50, 1000);
            $animal->decline_reason = $faker->sentence;
            $animal->origin_latitude = $faker->latitude;
            $animal->origin_longitude = $faker->longitude;
            $animal->address = $faker->address;
            $animal->phone_number = $faker->phoneNumber;
            $animal->has_parent = $faker->randomElement(['Yes', 'No']);
            $females = Animal::where([
                'sex' => 'Female'
            ])->get();

            if ($animal->has_parent == 'Yes' && count($females) > 1) {
                $mom_index = rand(1, count($females));
                $animal->parent_id = $females[$mom_index]->id;
            }
            $img_index = rand(1, count($latest_images));
            $animal->photo = $latest_images[$img_index]->src;
            $animal->stage = self::get_cattle_stage($animal->dob, $animal->sex);
            $animal->average_milk = rand(3, 10);
            $animal->weight_text = $faker->word;
            $animal->slaughter_house_id = $faker->numberBetween(1, 100);
            $animal->movement_id = $faker->numberBetween(1, 100);
            $animal->has_more_info = $faker->randomElement(['Yes', 'No']);
            $animal->was_purchases = $faker->randomElement(['Yes', 'No']);
            $animal->purchase_date = $faker->date();
            $animal->purchase_from = $faker->name;
            $animal->purchase_price = rand(100, 5000) . '000';
            $animal->current_price = rand(100, 5000) . '000';
            $animal->weight_at_birth = rand(20, 60);
            $animal->conception = $faker->randomElement(['Yes', 'No']);
            $animal->genetic_donor = str_pad($i, 10, '0', STR_PAD_LEFT);
            $animal->group_id = $farm_group->id;
            $animal->comments = $faker->text;
            $animal->local_id = self::get_unique_text();
            $animal->registered_by_id = $u->id;
            $animal->has_fmd = $faker->randomElement(['Yes', 'No']);
            $animal->registered_id = $farm_group->id;
            $animal->weight_change = $faker->randomFloat(2, -20, 20);
            $animal->has_produced_before = $faker->randomElement(['Yes', 'No']);
            if ($animal->sex == 'Male') {
                $animal->has_produced_before = 'No';
            }
            $animal->age_at_first_calving = $faker->numberBetween(1, 10);
            $animal->weight_at_first_calving = $faker->randomFloat(2, 100, 1000);
            $animal->has_been_inseminated = $faker->randomElement(['Yes', 'No']);
            $animal->age_at_first_insemination = $faker->numberBetween(1, 10);
            $animal->weight_at_first_insemination = $faker->randomFloat(2, 100, 1000);
            $animal->inter_calving_interval = $faker->numberBetween(1, 10);
            $animal->calf_mortality_rate = $faker->randomFloat(2, 0, 1);
            $animal->weight_gain_per_day = $faker->randomFloat(2, 0, 1);
            $animal->number_of_isms_per_conception = $faker->numberBetween(1, 10);
            $animal->is_a_calf = $faker->randomElement(['Yes', 'No']);
            $animal->is_weaned_off = $faker->randomElement(['Yes', 'No']);
            $animal->wean_off_weight = $faker->randomFloat(2, 50, 100);
            $animal->wean_off_age = $faker->numberBetween(1, 10);
            $animal->last_profile_update_date = $faker->date();
            $animal->profile_updated = $faker->randomElement(['Yes', 'No']);
            $animal->birth_position = $faker->numberBetween(1, 10);
            $animal->age = $faker->numberBetween(1, 20);
            try {
                $animal->save();
            } catch (\Throwable $th) {
                echo "SKIPPED BECAUSE: " . $th->getMessage() . "<br>";
            }
            echo "Animal $i created successfully<br>";
        }
        die();
    }

    //run test
    public static function run_test()
    {
        $_100_animals = Animal::where([])
            ->latest()
            ->limit(2500)
            ->get();
        foreach ($_100_animals as $key => $animal) {
            $animal->details .= ".";
            $animal->save();
        }
    }

    public static function COUNTRIES()
    {
        $data = [];
        foreach ([
            '',
            "Kenya",
            "Tanzania",
            "Rwanda",
            "Congo",
            "Somalia",
            "Sudan",
            "Afghanistan",
            "Albania",
            "Algeria",
            "American Samoa",
            "Andorra",
            "Angola",
            "Anguilla",
            "Antarctica",
            "Antigua and Barbuda",
            "Argentina",
            "Armenia",
            "Aruba",
            "Australia",
            "Austria",
            "Azerbaijan",
            "Bahamas",
            "Bahrain",
            "Bangladesh",
            "Barbados",
            "Belarus",
            "Belgium",
            "Belize",
            "Benin",
            "Bermuda",
            "Bhutan",
            "Bolivia",
            "Bosnia and Herzegovina",
            "Botswana",
            "Bouvet Island",
            "Brazil",
            "British Indian Ocean Territory",
            "Brunei Darussalam",
            "Bulgaria",
            "Burkina Faso",
            "Burundi",
            "Cambodia",
            "Cameroon",
            "Canada",
            "Cape Verde",
            "Cayman Islands",
            "Central African Republic",
            "Chad",
            "Chile",
            "China",
            "Christmas Island",
            "Cocos (Keeling Islands)",
            "Colombia",
            "Comoros",
            "Cook Islands",
            "Costa Rica",
            "Cote D'Ivoire (Ivory Coast)",
            "Croatia (Hrvatska",
            "Cuba",
            "Cyprus",
            "Czech Republic",
            "Denmark",
            "Djibouti",
            "Dominica",
            "Dominican Republic",
            "East Timor",
            "Ecuador",
            "Egypt",
            "El Salvador",
            "Equatorial Guinea",
            "Eritrea",
            "Estonia",
            "Ethiopia",
            "Falkland Islands (Malvinas)",
            "Faroe Islands",
            "Fiji",
            "Finland",
            "France",
            "France",
            "Metropolitan",
            "French Guiana",
            "French Polynesia",
            "French Southern Territories",
            "Gabon",
            "Gambia",
            "Georgia",
            "Germany",
            "Ghana",
            "Gibraltar",
            "Greece",
            "Greenland",
            "Grenada",
            "Guadeloupe",
            "Guam",
            "Guatemala",
            "Guinea",
            "Guinea-Bissau",
            "Guyana",
            "Haiti",
            "Heard and McDonald Islands",
            "Honduras",
            "Hong Kong",
            "Hungary",
            "Iceland",
            "India",
            "Indonesia",
            "Iran",
            "Iraq",
            "Ireland",
            "Israel",
            "Italy",
            "Jamaica",
            "Japan",
            "Jordan",
            "Kazakhstan",

            "Kiribati",
            "Korea (North)",
            "Korea (South)",
            "Kuwait",
            "Kyrgyzstan",
            "Laos",
            "Latvia",
            "Lebanon",
            "Lesotho",
            "Liberia",
            "Libya",
            "Liechtenstein",
            "Lithuania",
            "Luxembourg",
            "Macau",
            "Macedonia",
            "Madagascar",
            "Malawi",
            "Malaysia",
            "Maldives",
            "Mali",
            "Malta",
            "Marshall Islands",
            "Martinique",
            "Mauritania",
            "Mauritius",
            "Mayotte",
            "Mexico",
            "Micronesia",
            "Moldova",
            "Monaco",
            "Mongolia",
            "Montserrat",
            "Morocco",
            "Mozambique",
            "Myanmar",
            "Namibia",
            "Nauru",
            "Nepal",
            "Netherlands",
            "Netherlands Antilles",
            "New Caledonia",
            "New Zealand",
            "Nicaragua",
            "Niger",
            "Nigeria",
            "Niue",
            "Norfolk Island",
            "Northern Mariana Islands",
            "Norway",
            "Oman",
            "Pakistan",
            "Palau",
            "Panama",
            "Papua New Guinea",
            "Paraguay",
            "Peru",
            "Philippines",
            "Pitcairn",
            "Poland",
            "Portugal",
            "Puerto Rico",
            "Qatar",
            "Reunion",
            "Romania",
            "Russian Federation",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Vincent and The Grenadines",
            "Samoa",
            "San Marino",
            "Sao Tome and Principe",
            "Saudi Arabia",
            "Senegal",
            "Seychelles",
            "Sierra Leone",
            "Singapore",
            "Slovak Republic",
            "Slovenia",
            "Solomon Islands",
            "South Africa",
            "South Sudan",
            "S. Georgia and S. Sandwich Isls.",
            "Spain",
            "Sri Lanka",
            "St. Helena",
            "St. Pierre and Miquelon",
            "Suriname",
            "Svalbard and Jan Mayen Islands",
            "Swaziland",
            "Sweden",
            "Switzerland",
            "Syria",
            "Taiwan",
            "Tajikistan",
            "Thailand",
            "Togo",
            "Tokelau",
            "Tonga",
            "Trinidad and Tobago",
            "Tunisia",
            "Turkey",
            "Turkmenistan",
            "Turks and Caicos Islands",
            "Tuvalu",
            "Ukraine",
            "United Arab Emirates",
            "United Kingdom (Britain / UK)",
            "United States of America (USA)",
            "US Minor Outlying Islands",
            "Uruguay",
            "Uzbekistan",
            "Vanuatu",
            "Vatican City State (Holy See)",
            "Venezuela",
            "Viet Nam",
            "Virgin Islands (British)",
            "Virgin Islands (US)",
            "Wallis and Futuna Islands",
            "Western Sahara",
            "Yemen",
            "Yugoslavia",
            "Zaire",
            "Zambia",
            "Zimbabwe"
        ] as $key => $v) {
            $data[$v] = $v;
        };
        return $data;
    }
}

//724