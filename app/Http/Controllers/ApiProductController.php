<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\District;
use App\Models\DrugForSale;
use App\Models\Event;
use App\Models\Utils;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Transaction;
use App\Models\Vet;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;

class ApiProductController extends Controller
{



    public function orders(Request $r)
    {


        $items = [];
        $per_page = 1000;
        if (
            isset($r->per_page) &&
            $r->per_page != null
        ) {
            $per_page = ((int)($r->per_page));
        }


        $administrator_id = ((int) (Utils::get_user_id($r)));
        $u = Administrator::find($administrator_id);

        $conds = [];

        foreach ($_GET as $key => $value) {
            if (strlen($key) < 3) {
                continue;
            }
            if (substr($key, 0, 6) == 'param_') {
                $_key = str_replace(substr($key, 0, 6), "", $key);
                $conds[$_key] = $value;
            }
        }



        if ($u != null) {
            $role = Utils::get_role($u);
            if ($role == 'order-processing') {
                $items =
                    ProductOrder::where([])
                    ->orderBy('id', 'DESC')
                    ->paginate($per_page)->withQueryString()->items();
            } else {

                $items =
                    ProductOrder::where([
                        'customer_id' => $u->id
                    ])
                    ->orderBy('id', 'DESC')
                    ->paginate($per_page)->withQueryString()->items();
            }
        }


        return Utils::response([
            'status' => 1,
            'data' => $items,
            'message' => "Success."
        ]);
    }

    public function products_decline_request(Request $r)
    {
        if (
            (!isset($r->id)) ||
            (!isset($r->reason))
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must submit all required information."
            ]);
        }
        $p = Animal::find($r->id);

        if ($p == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Product not found."
            ]);
        }
        $p->for_sale = '11';
        $p->decline_reason = $r->reason;
        $p->save();


        return Utils::response([
            'status' => 1,
            'message' => "Request declined successfully."
        ]);
    }


    public function products_create_request(Request $r)
    {
        if (
            (!isset($r->animal_id)) ||
            (!isset($r->latitude)) ||
            (!isset($r->phone_number)) ||
            (!isset($r->longitude))
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must submit all required information."
            ]);
        }
        $p = Animal::find($r->animal_id);

        if ($p == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animal not found."
            ]);
        }

        $p->for_sale = '20';
        $p->origin_latitude = $r->latitude;
        $p->origin_longitude = $r->longitude;
        $p->phone_number = $r->phone_number;
        $p->save();

        return Utils::response([
            'status' => 1,
            'message' => "Request submitted successfully."
        ]);
    }
    /* 
created_at	
updated_at	
administrator_id	
district_id	
sub_county_id	
parish_id	
status	
type	
e_id	
v_id	
lhc	
breed	
sex	
dob	
color	
farm_id	
fmd	
trader	
destination	
destination_slaughter_house	
destination_farm	
details	
deleted_at	
for_sale	
price	
weight	
decline_reason	
	
	
address
*/
    public function drugs_order_create(Request $r)
    {
  
        if (
            (!isset($r->product_id)) ||
            (!isset($r->name)) ||
            (!isset($r->phone_number)) ||
            (!isset($r->address)) ||
            (!isset($r->note))
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must submit all required information."
            ]);
        }


        $administrator_id = ((int) (Utils::get_user_id($r)));
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found."
            ]);
        }

        $drug = DrugForSale::find(((int)($r->product_id)));
        if ($drug == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Drugs not found."
            ]);
        }

 
        $p = new ProductOrder();
        $p->status = 1;
        $p->customer_id = $u->id;
        $p->address = $r->id;
        $p->note = $r->note;
        $p->name = $r->name;
        $p->phone_number = $r->phone_number;
        $p->product_id = $drug->id;
        $p->latitude = $r->latitude;
        $p->longitude = $r->longitude;
        $p->type = 'Drug';
        $p->product_data = json_encode($drug);
        $p->customer_data = json_encode($u);
        if ($p->save()) {
            return Utils::response([
                'status' => 1,
                'data' => $p,
                'message' => "Order submitted successfully."
            ]);
        } else {
            return Utils::response([
                'status' => 0, 
                'data' => null,
                'message' => "Failed to submit order. Please try gain."
            ]);
        }
    }


    public function product_order_create(Request $r)
    {
        if (
            (!isset($r->product_id)) ||
            (!isset($r->name)) ||
            (!isset($r->phone_number)) ||
            (!isset($r->address)) ||
            (!isset($r->note))
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must submit all required information."
            ]);
        }


        $administrator_id = ((int) (Utils::get_user_id($r)));
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found."
            ]);
        }

        $animal = Animal::find(((int)($r->product_id)));
        if ($animal == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animal not found."
            ]);
        }


        $p = new ProductOrder();
        $p->status = 1;
        $p->customer_id = $u->id;
        $p->address = $r->id;
        $p->note = $r->note;
        $p->type = 'Animal';
        $p->name = $r->name;
        $p->phone_number = $r->phone_number;
        $p->product_id = $animal->id;
        $p->latitude = $animal->latitude;
        $p->longitude = $animal->longitude;
        $p->product_data = json_encode($animal);
        $p->customer_data = json_encode($u);
        if ($p->save()) {
            return Utils::response([
                'status' => 1,
                'data' => $p,
                'message' => "Order submitted successfully."
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => $p,
                'message' => "Failed to submit order. Please try gain."
            ]);
        }
    }

    public function product_upload(Request $r)
    {
        $administrator_id = ((int) (Utils::get_user_id($r)));
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found."
            ]);
        }

        $animal = Animal::find(((int)($r->animal_id)));
        if ($animal == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animal not found."
            ]);
        }
        if (
            $r->price == null ||
            $r->weight == null ||
            $r->details == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Some parameters are missing."
            ]);
        }

        Utils::process_images_in_foreround();

        $animal->price = ((int)($r->price));
        $animal->weight = $r->weight;
        $animal->details = trim($r->details);
        $animal->for_sale = 1;
        $animal->save();


        $imgs = Image::where([
            'administrator_id' => $administrator_id,
            'parent_id' => null,
        ])->get();

        foreach ($imgs as $img) {
            $img->parent_id = $animal->id;
            $img->type = 'animal';
            $img->save();
        }

        return Utils::response([
            'status' => 1,
            'message' => "Animal posted successfully."
        ]);
    }


    public function product_drugs_list(Request $r){
        return Utils::response([
            'status' => 1,
            'data' => DrugForSale::where([])->get(), 
            'message' => "Item uploaded successfully."
        ]);
    }
    public function product_drugs_upload(Request $r)
    {
        $administrator_id = ((int) (Utils::get_user_id($r)));
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found."
            ]);
        }

        if ($u->vet_profile == null) {
            return Utils::response([
                'status' => 0,
                'message' => "You need vet profile to post a drug or service."
            ]);
        }


        if (
            $r->selling_price == null ||
            $r->drug_category_id == null ||
            $r->name == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Some parameters are missing."
            ]);
        }

        Utils::process_images_in_foreround();
        $d = new DrugForSale();
        $d->administrator_id = $u->id;
        $d->drug_category_id = $r->drug_category_id;
        $d->manufacturer = $r->manufacturer;
        $d->ingredients = $r->ingredients;
        $d->batch_number = $r->batch_number;
        $d->name = $r->name;
        $d->vet_id = $u->vet_profile->id;
        $d->expiry_date = $r->expiry_date;
        $d->original_quantity = $r->original_quantity;
        $d->selling_price = $r->selling_price;
        $d->details = $r->details;
        $d->save();
        $imgs = Image::where([
            'administrator_id' => $administrator_id,
            'parent_id' => null,
        ])->get();

        foreach ($imgs as $v) {
            $v->parent_id = $d->id;
            $v->product_id = $d->id;
            $v->type = 'DrugForSale';
            $v->parent_endpoint = 'DrugForSale';
            $v->note = $d->name;
            $v->save();
        }

        return Utils::response([
            'status' => 1,
            'message' => "Item uploaded successfully."
        ]);
    }



    public function product_image_upload(Request $request)
    {
        $administrator_id = ((int) (Utils::get_user_id($request)));
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found."
            ]);
        }
        $images = Utils::upload_images_2($_FILES, false);
        foreach ($images as $src) {
            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  null;
            $img->parent_id =  null;
            $img->save();
        }
        Utils::process_images_in_backround();
        return Utils::response([
            'status' => 1,
            'data' => $images,
            'message' => "Image successfully."
        ]);
    }

    public function process_pending_images()
    {
        Utils::process_images_in_foreround();
        return 1;
    }

    public function milk(Request $r)
    {
        $administrator_id = ((int) (Utils::get_user_id($r)));
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found."
            ]);
        }

        $data = [];
        $records = [];
        $prev = 0;
        for ($i = 29; $i >= 0; $i--) {
            $min = new Carbon();
            $max = new Carbon();
            $max->subDays($i);
            $min->subDays(($i + 1));

            //2022-11-03 19:33:51.955979 UTC (+00:00)

            //2022-11-03 00:00:00.0 UTC (+00:00)
            //2022-11-02 00:00:00.0 UTC (+00:00)


            $max = Carbon::parse($max->format('Y-m-d'));
            $min = Carbon::parse($min->format('Y-m-d'));


            $milk = Event::whereBetween('created_at', [$min, $max])
                ->where([
                    'type' => 'Milking'
                ])
                ->sum('milk');

            $count = Event::whereBetween('created_at', [$min, $max])
                ->where([
                    'type' => 'Milking'
                ])
                ->count('animal_id');


            $expence = Transaction::whereBetween('created_at', [$min, $max])
                ->where([
                    'is_income' => 0
                ])
                ->sum('amount');

            $income = Transaction::whereBetween('created_at', [$min, $max])
                ->where([
                    'is_income' => 1
                ])
                ->sum('amount');

            $data['data'][] = $milk;
            $data['income'][] = $income;
            $data['count'][] = $count;
            $data['expence'][] = ((-1) * ($expence));
            $data['labels'][] = Utils::my_day($max);

            $rec['day'] = Utils::my_date_1($max);
            $rec['animals'] = $count;
            $rec['milk'] = $milk;

            $rec['progress'] = 0;
            if ($count > 0) {
                $avg = $milk / $count;
                $rec['progress'] =  $avg - $prev;
                $prev = $avg;
            }

            $data['records'][] = $rec;
        }

        $data['records'] = array_reverse($data['records']);

        $_data['data'] = json_encode($data);

        return Utils::response([
            'status' => 0,
            'data' => $_data,
            'message' => "User not found."
        ]);
    }
    public function products(Request $r)
    {

        $per_page = 1000;
        if (
            isset($r->per_page) &&
            $r->per_page != null
        ) {
            $per_page = ((int)($r->per_page));
        }

        if (
            isset($r->per_page) &&
            $r->per_page != null
        ) {
            $per_page = ((int)($r->per_page));
        }
        $for_sale = 1;

        if (
            isset($r->for_sale) &&
            $r->for_sale != null
        ) {
            $for_sale = ((int)($r->for_sale));
        }

        $conds = [];

        foreach ($_GET as $key => $value) {
            if (strlen($key) < 3) {
                continue;
            }
            if (substr($key, 0, 6) == 'param_') {
                $_key = str_replace(substr($key, 0, 6), "", $key);
                $conds[$_key] = $value;
            }
        }


        $items =
            Animal::where([
                'for_sale' => $for_sale
            ])
            ->orderBy('id', 'DESC')
            ->where($conds)
            ->paginate($per_page)->withQueryString()->items();

        //$items = Animal::paginate($per_page)->withQueryString()->items();

        return Utils::response([
            'status' => 1,
            'data' => $items,
            'message' => "Image successfully."
        ]);
    }

    public function products_pending_for_verification(Request $r)
    {

        $per_page = 1000;
        if (
            isset($r->per_page) &&
            $r->per_page != null
        ) {
            $per_page = ((int)($r->per_page));
        }


        $items =
            Animal::where([
                'for_sale' => 20
            ])
            ->paginate($per_page)->withQueryString()->items();
        //$items = Animal::paginate($per_page)->withQueryString()->items();

        return Utils::response([
            'status' => 1,
            'data' => $items,
            'message' => "Image successfully."
        ]);
    }
}
