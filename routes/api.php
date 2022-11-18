<?php
//rominah j
use App\Admin\Controllers\FarmController;
use App\Http\Controllers\ApiAnimalController;
use App\Http\Controllers\ApiEventController;
use App\Http\Controllers\ApiFarmController;
use App\Http\Controllers\ApiLoginController;
use App\Http\Controllers\ApiMovement;
use App\Http\Controllers\ApiProductController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\UtilsController;
use App\Http\Controllers\ApiUserController;
use App\Models\Animal;
use App\Models\Disease;
use App\Models\Location;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::post('product-image-upload', [ApiProductController::class, 'product_image_upload']);
Route::post('product-upload', [ApiProductController::class, 'product_upload']);
Route::post('product-order', [ApiProductController::class, 'product_order_create']);
Route::get('order', [ApiProductController::class, 'orders']);

Route::get('process-pending-images', [ApiProductController::class, 'process_pending_images']);
Route::get('products', [ApiProductController::class, 'products']);
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
Route::post('create-sale', [ApiAnimalController::class, 'create_sale']);
Route::get('slaughters', [ApiAnimalController::class, 'slaughters']);
Route::get('animals', [ApiAnimalController::class, 'index']);
Route::post('create-slaughter', [ApiAnimalController::class, 'create_slaughter']);
Route::get('animals/{id}', [ApiAnimalController::class, 'show']);

Route::get('events', [ApiAnimalController::class, 'events']);
Route::post('events', [ApiAnimalController::class, 'store_event']);
Route::get('events/{id}', [ApiAnimalController::class, 'show']);
// Animal controler //

// ========== users starts ============== //
Route::post('farms', [ApiFarmController::class, 'create']);
Route::get('farms/{id}', [ApiFarmController::class, 'show']);
Route::get('farms', [ApiFarmController::class, 'index']);
Route::get('locations', [ApiFarmController::class, 'locations']);


Route::get('users', [ApiUserController::class, 'index']);
Route::get('movements', [ApiMovement::class, 'index']);
Route::post('movements', [ApiMovement::class, 'create']);
Route::post('check-point-records', [ApiMovement::class, 'create_check_record']);
Route::get('check-point-records', [ApiMovement::class, 'get_check_record']);
Route::post('movements', [ApiMovement::class, 'create']);
Route::get('movements/{id}', [ApiMovement::class, 'show']);
Route::post('users', [ApiLoginController::class, 'create_account']);
// ========== users ends ============== //


// ========== lofin starts ============== //
Route::post('login', [ApiLoginController::class, 'index']);
Route::post('login/create-account', [ApiLoginController::class, 'create_account']);
// ========== lofin ends ============== //


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



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
            'text' => "$v->phone_number" . $name
        ];
    }
    foreach ($res_2 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name =   $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "$v->phone_number" . $name
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
        $data[] = [
            'id' => $v->id,
            'text' => "$v->name_text"
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
