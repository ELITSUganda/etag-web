<?php
//rominah j
use App\Admin\Controllers\FarmController;
use App\Http\Controllers\ApiAnimalController;
use App\Http\Controllers\ApiEventController;
use App\Http\Controllers\ApiFarmController;
use App\Http\Controllers\ApiLoginController;
use App\Http\Controllers\ApiMovement;
use App\Http\Controllers\ApiProductController;
use App\Http\Controllers\ApiResurceController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\UtilsController;
use App\Http\Controllers\ApiUserController;
use App\Models\Animal;
use App\Models\Disease;
use App\Models\Location;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('drug-categories', [ApiMovement::class, 'drug_categories']);
Route::get('api/Movement', [ApiMovement::class, 'index']);
Route::get('roll-calls', [ApiResurceController::class, 'roll_call']);
Route::get('manifest', [ApiResurceController::class, 'manifest']);
Route::get('daily-milk-records', [ApiResurceController::class, 'dialy_milk_records']);
Route::get('api/{model}', [ApiResurceController::class, 'index']);
Route::post('drug-dosages', [ApiResurceController::class, 'save_new_drug_dosage']);
Route::post('api/{model}', [ApiResurceController::class, 'store']);
Route::put('api/{model}', [ApiResurceController::class, 'update']);
Route::delete('api/{model}', [ApiResurceController::class, 'delete']);

//Route::resource('api', [ApiResurceController::class, 'product_image_upload']);

Route::post('product-image-upload', [ApiProductController::class, 'product_image_upload']);

Route::post('product-upload', [ApiProductController::class, 'product_upload']);
Route::post('product-drugs-upload', [ApiProductController::class, 'product_drugs_upload']);
Route::get('product-drugs', [ApiProductController::class, 'product_drugs_list']);
Route::post('product-order', [ApiProductController::class, 'product_order_create']);
Route::post('drugs-order', [ApiProductController::class, 'drugs_order_create']);
Route::get('order', [ApiProductController::class, 'orders']);

Route::get('process-pending-images', [ApiProductController::class, 'process_pending_images']);
Route::get('products', [ApiProductController::class, 'products']);
Route::get('milk', [ApiProductController::class, 'milk']);
Route::post('products-decline-request', [ApiProductController::class, 'products_decline_request']);
Route::post('products-create-request', [ApiProductController::class, 'products_create_request']);
//Route::get('products-pending-for-verification', [ApiProductController::class, 'products_pending_for_verification']);



Route::get('districts', [DistrictController::class, 'index']);
Route::get('districts/{id}', [DistrictController::class, 'show']);
Route::post('districts', [DistrictController::class, 'store']);
Route::put('districts/{id}', [DistrictController::class, 'update']);
Route::delete('districts/{id}', [DistrictController::class, 'delete']);

// sub_counties
Route::get('sub_counties', [UtilsController::class, 'sub_counties']);
//Utils


// animal //
Route::post('animals', [ApiAnimalController::class, 'create']);
Route::post('animals-update', [ApiAnimalController::class, 'create_update']);
Route::post('create-sale', [ApiAnimalController::class, 'create_sale']);
Route::get('slaughters', [ApiAnimalController::class, 'slaughters']);
Route::get('slaughter-houses', [ApiAnimalController::class, 'slaughter_houses']);

Route::get('animals', [ApiAnimalController::class, 'index']);
Route::get('transporters-v2', [ApiAnimalController::class, 'transporters']);
Route::get('animals-v2', [ApiAnimalController::class, 'index_v2']);
Route::get('images-v2', [ApiAnimalController::class, 'images_v2']);

Route::get('photos-downloads', [ApiAnimalController::class, 'photo_downloads']);
Route::post('create-slaughter', [ApiAnimalController::class, 'create_slaughter']);
Route::get('animals/{id}', [ApiAnimalController::class, 'show']);

Route::get('events', [ApiAnimalController::class, 'events']);
Route::get('events-v2', [ApiAnimalController::class, 'events_v2']);
Route::post('events', [ApiAnimalController::class, 'store_event']);
Route::post('batch-events', [ApiAnimalController::class, 'store_batch_event']);
Route::post('batch-events-create', [ApiAnimalController::class, 'batch_events_create']);
Route::get('events/{id}', [ApiAnimalController::class, 'show']);
// Animal controler //

// ========== users starts ============== //
Route::post('farms', [ApiFarmController::class, 'create']);
Route::get('farms/{id}', [ApiFarmController::class, 'show']);
Route::get('farms', [ApiFarmController::class, 'index']);
Route::get('my-drugs', [ApiFarmController::class, 'my_drugs']);
Route::get('locations', [ApiFarmController::class, 'locations']);


Route::get('users', [ApiUserController::class, 'index']);
Route::get('movements', [ApiMovement::class, 'index']);
Route::get('user-roles', [ApiMovement::class, 'user_roles']);
Route::get('movement-routes', [ApiMovement::class, 'routes']);
Route::get('system-users', [ApiMovement::class, 'system_users']);


Route::post('transfer-animal/{id}', [ApiMovement::class, 'transfer_animal']);
Route::post('checkpoint-session/{id}', [ApiMovement::class, 'checkpoint_session']);
Route::post('checkpoint-verification', [ApiMovement::class, 'checkpoint_verification']);
Route::post('slaughter-session', [ApiMovement::class, 'slaughter_session']);
Route::post('movements-review/{id}', [ApiMovement::class, 'review']);
Route::post('change-tag/{id}', [ApiAnimalController::class, 'change_tag']);
Route::post('archive-animal/{id}', [ApiAnimalController::class, 'archive_animal']);

Route::post('check-point-records', [ApiMovement::class, 'create_check_record']);
Route::get('check-point-records', [ApiMovement::class, 'get_check_record']);
Route::post('movements', [ApiMovement::class, 'create']);
Route::post('movements-v2', [ApiMovement::class, 'create_v2']);
Route::post('trips-v2', [ApiMovement::class, 'trip_create_v2']);
Route::get('trips-v2', [ApiMovement::class, 'trips_v2']);
Route::get('movements/{id}', [ApiMovement::class, 'show']);
Route::post('trip-records', [ApiMovement::class, 'create_trip_record']);
Route::post('trip-end', [ApiMovement::class, 'trip_end']);
Route::post('users', [ApiLoginController::class, 'create_account']);
// ========== users ends ============== //


// ========== lofin starts ============== //
Route::post('login', [ApiLoginController::class, 'index']);
Route::post('update-roles', [ApiLoginController::class, 'update_roles']);
Route::post('remove-vet-role', [ApiLoginController::class, 'remove_vet_role']);
Route::post('vet-profile', [ApiLoginController::class, 'vet_profile']);
Route::post('update-profile', [ApiLoginController::class, 'update_profile']);
Route::get('me', [ApiLoginController::class, 'me']);
Route::post('login/create-account', [ApiLoginController::class, 'create_account']);
// ========== lofin ends ============== //



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("post-media-upload", [ApiAnimalController::class, 'upload_media']);


Route::get('ajax', function (Request $r) {

    $_model = trim($r->get('model'));

    if (strlen($_model) < 2) {
        return [
            'data' => []
        ];;
    }

    $model = "App\Models\\" . $_model;
    $search_by_1 = trim($r->get('search_by_1'));
    $search_by_2 = trim($r->get('search_by_2'));

    $q = trim($r->get('q'));
    if (strlen($q) < 1) {
        return [
            'data' => []
        ];
    }
    $res_1 = $model::where(
        $search_by_1,
        'like',
        "%$q%"
    )
        ->limit(20)->get();
    $res_2 = [];

    if ((count($res_1) < 20) && (strlen($search_by_2) > 1)) {
        $res_2 = $model::where(
            $search_by_2,
            'like',
            "%$q%"
        )
            ->limit(20)->get();
    }

    $data = [];
    foreach ($res_1 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name =  $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' =>  $name . " - $v->phone_number "
        ];
    }
    foreach ($res_2 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name =   $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' =>  $name . " -  $v->phone_number"
        ];
    }

    return [
        'data' => $data
    ];
});



Route::get('diseases', function (Request $r) {
    $data = Disease::all();

    return Utils::response([
        'status' => 1,
        'message' => "Event was created successfully.",
        'data' => $data
    ]);
});

Route::get('sub-counties', function (Request $r) {

    $q = trim($r->get('q'));
    if (strlen($q) < 1) {
        return [
            'data' => []
        ];
    }
    $res_1 = Location::where(
        'name',
        'like',
        "%$q%"
    )
        ->orWhere(
            'name',
            'like',
            "%$q%"
        )
        ->where('parent', '!=', 0)
        ->limit(20)->get();
    $data = [];
    foreach ($res_1 as $key => $v) {
        if ($v->parent == 0) {
            continue;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "$v->name_text"
        ];
    }

    return [
        'data' => $data
    ];
});


Route::get('districts', function (Request $r) {

    $q = trim($r->get('q'));
    if (strlen($q) < 1) {
        return [
            'data' => []
        ];
    }
    $res_1 = Location::where(
        'name',
        'like',
        "%$q%"
    )
        ->orWhere(
            'name',
            'like',
            "%$q%"
        )
        ->where('parent', '==', 0)
        ->limit(20)->get();
    $data = [];
    foreach ($res_1 as $key => $v) {
        $data[] = [
            'id' => $v->id,
            'text' => "$v->name"
        ];
    }

    return [
        'data' => $data
    ];
});



Route::get('ajax-animals', function (Request $r) {

    $administrator_id = 0;
    if (isset($r->administrator_id)) {
        if ($r->administrator_id != null) {
            $administrator_id = ((int)($r->administrator_id));
        }
    }

    $q = trim($r->get('q'));
    if (strlen($q) < 1) {
        return [
            'data' => []
        ];
    }
    $res_1 = Animal::where(
        'e_id',
        'like',
        "%$q%"
    )
        ->where('administrator_id', $administrator_id)
        ->limit(20)->get();

    $res_2 = Animal::where(
        'v_id',
        'like',
        "%$q%"
    )
        ->where('administrator_id', $administrator_id)
        ->limit(20)->get();


    $data = [];
    $done_ids = [];

    foreach ($res_1 as $key => $v) {
        if (in_array($v->id, $done_ids)) {
            continue;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "{$v->e_id} - {$v->v_id}"
        ];
        $done_ids[] = $v->id;
    }

    foreach ($res_2 as $key => $v) {
        $data[] = [
            'id' => $v->id,
            'text' => "{$v->e_id} - {$v->v_id}"
        ];
        $done_ids[] = $v->id;
    }

    return [
        'data' => $data
    ];
});


Route::get('ajax-users', function (Request $r) {
    $q = trim($r->get('q'));
    if (strlen($q) < 1) {
        return [
            'data' => []
        ];
    }
    $res_1 = Administrator::where(
        'name',
        'like',
        "%$q%"
    )
        ->limit(20)->get();

    $data = [];
    $done_ids = [];

    foreach ($res_1 as $key => $v) {
        if (in_array($v->id, $done_ids)) {
            continue;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "{$v->name} - #{$v->id}"
        ];
        $done_ids[] = $v->id;
    }

    return [
        'data' => $data
    ];
});
Route::get('wholesellers', function (Request $r) {
    $q = trim($r->get('q'));
    if (strlen($q) < 1) {
        return [
            'data' => []
        ];
    }
    $res_1 = Administrator::where(
        'name',
        'like',
        "%$q%"
    )
        ->limit(20)->get();

    $data = [];
    $done_ids = [];

    foreach ($res_1 as $key => $v) {
        if (in_array($v->id, $done_ids)) {
            continue;
        }
        if (!$v->isRole('drugs-wholesaler')) {
            continue;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "{$v->name} - #{$v->id} "
        ];
        $done_ids[] = $v->id;
    }

    return [
        'data' => $data
    ];
});
