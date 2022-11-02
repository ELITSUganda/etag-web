<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\District;
use App\Models\Utils;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductOrder;
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
        if ($u != null) {
            if ($u->user_type == 'admin') {
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
        $p->name = $r->name;
        $p->phone_number = $r->phone_number;
        $p->product_id = $animal->id;
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
        $images = Utils::upload_images_1($_FILES, false);
        foreach ($images as $src) {
            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  null;
            $img->parent_id =  null;
            $img->size = filesize('public/storage/images/' . $img->src);
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

        $items =
            Animal::where([
                'for_sale' => $for_sale
            ])
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
