<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\District;
use App\Models\Utils;
use App\Models\Image;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;

class ApiProductController extends Controller
{



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

        $per_page = 1;
        if (
            isset($r->per_page) &&
            $r->per_page != null
        ) {
            $per_page = ((int)($r->per_page));
        }
        $items = 
        Animal::where(/* [
            'for_sale' => 1
         ]*/)
        ->paginate($per_page)->withQueryString()->items();


        return Utils::response([
            'status' => 1,
            'data' => $items,
            'message' => "Image successfully."
        ]);
    
    }
}
