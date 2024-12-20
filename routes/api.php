<?php
//rominah j
use App\Admin\Controllers\FarmController;
use App\Admin\Controllers\VaccinationScheduleController;
use App\Http\Controllers\ApiAnimalController;
use App\Http\Controllers\ApiEventController;
use App\Http\Controllers\ApiFarmController;
use App\Http\Controllers\ApiLoginController;
use App\Http\Controllers\ApiMovement;
use App\Http\Controllers\ApiProductController;
use App\Http\Controllers\ApiResurceController;
use App\Http\Controllers\ApiShopController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\UtilsController;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\V2ApiMainController;
use App\Models\Animal;
use App\Models\Disease;
use App\Models\Location;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('test-1', function (Request $r) {
    die("test GET #1");
});
Route::post('test-2', function (Request $r) {
    die("test POST #1");
});

Route::POST('v2-farms-create', [V2ApiMainController::class, 'v2_farms_create']);
Route::POST('v2-farm-report-create', [V2ApiMainController::class, 'v2_farm_report_create']);
Route::POST('v2-animals-create', [V2ApiMainController::class, 'v2_animals_create']);
Route::POST('v2-post-media-upload', [V2ApiMainController::class, 'v2_post_media_upload']);
Route::POST('v2-pregnant-animals-create', [V2ApiMainController::class, 'v2_pregnant_animals_create']);
Route::GET('v2-pregnant-animals', [V2ApiMainController::class, 'v2_pregnant_animals_list']);
Route::GET('v2-farm-reports', [V2ApiMainController::class, 'v2_farm_reports']);



Route::resource('vaccination-schedules', VaccinationScheduleController::class);

Route::POST('vaccination-order-create', [ApiMovement::class, 'vaccination_order_create']);
Route::POST('drug-product-create', [ApiMovement::class, 'drug_product_create']);
Route::POST('become-vendor', [ApiMovement::class, 'become_vendor']);
Route::POST('workers-register', [ApiMovement::class, 'workers_register']);
Route::POST('workers-delete', [ApiMovement::class, 'workers_delete']);
Route::get('fiance-report', [ApiMovement::class, 'fiance_report']);
Route::get('reports', [ApiMovement::class, 'reports']);
Route::get('workers', [ApiMovement::class, 'workers']);
Route::get('notifications', [ApiMovement::class, 'notifications']);
Route::POST('notifications-read', [ApiMovement::class, 'notifications_read']);
Route::get('fiance-categories', [ApiMovement::class, 'fiance_categories']);
Route::POST('finance-categories', [ApiMovement::class, 'finance_categories']);
Route::get('drug-categories', [ApiMovement::class, 'drug_categories']);
Route::get('users/me', [ApiMovement::class, 'users_me']);
Route::post('drug-categories', [ApiMovement::class, 'drug_categories_create']);
Route::get('api/Movement', [ApiMovement::class, 'index']);
Route::get('roll-calls', [ApiResurceController::class, 'roll_call']);
Route::get('archived-animals', [ApiResurceController::class, 'archived_animals']);
Route::get('groups', [ApiResurceController::class, 'groups']);
Route::get('manifest', [ApiResurceController::class, 'manifest']);
Route::get('daily-milk-records', [ApiResurceController::class, 'dialy_milk_records']);
Route::get('api/{model}', [ApiResurceController::class, 'index']);
Route::POST('drug-dosages', [ApiResurceController::class, 'save_new_drug_dosage']);
Route::POST('send-verification-code', [ApiResurceController::class, 'send_verification_code']);
Route::POST('reset-password', [ApiResurceController::class, 'reset_password']);
Route::POST('api/{model}', [ApiResurceController::class, 'store']);
Route::put('api/{model}', [ApiResurceController::class, 'update']);
Route::delete('api/{model}', [ApiResurceController::class, 'delete']);


//Route::resource('api', [ApiResurceController::class, 'product_image_upload']);

Route::POST('product-image-upload', [ApiProductController::class, 'product_image_upload']);
Route::POST('delete-account', [ApiProductController::class, 'delete_account']);
Route::POST('group-create', [ApiProductController::class, 'group_create']);

Route::POST('product-upload', [ApiProductController::class, 'product_upload']);
Route::POST('product-drugs-upload', [ApiProductController::class, 'product_drugs_upload']);
Route::get('product-drugs', [ApiProductController::class, 'product_drugs_list']);
Route::POST('product-order', [ApiProductController::class, 'product_order_create']);
Route::POST('product-order-payment-link-create', [ApiProductController::class, 'product_order_payment_link_create']);
Route::POST('vaccination-product-order-payment-link-create', [ApiProductController::class, 'vaccination_product_order_payment_link_create']);
Route::POST('product-order-verify', [ApiProductController::class, 'product_order_verify']);
Route::POST('vaccination-order-verify', [ApiProductController::class, 'vaccination_order_verify']);
Route::POST('drugs-order', [ApiProductController::class, 'drugs_order_create']);
Route::get('order', [ApiProductController::class, 'orders']);
Route::get('orders', [ApiProductController::class, 'get_orders']);

Route::get('process-pending-images', [ApiProductController::class, 'process_pending_images']);
Route::get('milk', [ApiProductController::class, 'milk']);
Route::POST('products-decline-request', [ApiProductController::class, 'products_decline_request']);
Route::POST('products-create-request', [ApiProductController::class, 'products_create_request']);
//Route::get('products-pending-for-verification', [ApiProductController::class, 'products_pending_for_verification']);



Route::get('districts', [DistrictController::class, 'index']);
Route::get('parishes', [DistrictController::class, 'parishes']);
Route::get('districts/{id}', [DistrictController::class, 'show']);
Route::POST('districts', [DistrictController::class, 'store']);
Route::put('districts/{id}', [DistrictController::class, 'update']);
Route::delete('districts/{id}', [DistrictController::class, 'delete']);

// sub_counties
Route::get('sub_counties', [UtilsController::class, 'sub_counties']);
//Utils


// animal //
Route::POST('animals', [ApiAnimalController::class, 'create']);
Route::POST('animals-update', [ApiAnimalController::class, 'create_update']);
Route::POST('create-sale', [ApiAnimalController::class, 'create_sale']);
Route::get('slaughters', [ApiAnimalController::class, 'slaughters']);
Route::get('cut-by-id', [ApiAnimalController::class, 'cut_by_id']);
Route::get('slaughter-distributions', [ApiAnimalController::class, 'slaughter_distributions']);
Route::get('slaughter-houses', [ApiAnimalController::class, 'slaughter_houses']);

Route::get('animals', [ApiAnimalController::class, 'index']);
Route::get('animals-small', [ApiAnimalController::class, 'animals_small']);
Route::get('transporters-v2', [ApiAnimalController::class, 'transporters']);
Route::get('animals-v2', [ApiAnimalController::class, 'index_v2']);
Route::get('images-v2', [ApiAnimalController::class, 'images_v2']);

Route::get('photos-downloads', [ApiAnimalController::class, 'photo_downloads']);
Route::POST('create-slaughter', [ApiAnimalController::class, 'create_slaughter']);
Route::POST('create-slaughter-single', [ApiAnimalController::class, 'create_slaughter_single']);
Route::POST('create-vaccination-schedules', [ApiAnimalController::class, 'create_vaccination_schedules']);
Route::POST('create-vaccination-programs', [ApiAnimalController::class, 'create_vaccination_programs']);
Route::POST('farm-vaccination-records-create', [ApiAnimalController::class, 'farm_vaccination_records_create']);
Route::POST('vaccination-session-submit', [ApiAnimalController::class, 'vaccination_session_submit']);
Route::get('vaccination-schedules-list', [ApiAnimalController::class, 'vaccination_schedules_list']);
Route::get('farm-vaccination-records', [ApiAnimalController::class, 'farm_vaccination_records']);
Route::get('district-vaccine-stocks', [ApiAnimalController::class, 'district_vaccine_stocks']);
Route::get('vaccination-programs', [ApiAnimalController::class, 'vaccination_programs']);
Route::POST('create-slaughter-distribution-record', [
    ApiAnimalController::class,
    'create_slaughter_distribution_record'
]);
Route::get('animals/{id}', [ApiAnimalController::class, 'show']);

Route::get('events', [ApiAnimalController::class, 'events']);
Route::get('events-v2', [ApiAnimalController::class, 'events_v2']);
Route::post('events-v2', [ApiAnimalController::class, 'store_event_2']);
Route::POST('events-v3', [ApiAnimalController::class, 'store_event_2']);
Route::POST('events-new', [ApiAnimalController::class, 'store_event_2']);
Route::POST('v3-animal-records', [ApiAnimalController::class, 'store_event_2']);
Route::get('events-v3', [ApiAnimalController::class, 'events_v3']);
Route::POST('events', [ApiAnimalController::class, 'store_event']);
Route::POST('batch-events', [ApiAnimalController::class, 'store_batch_event']);
Route::POST('batch-events-create', [ApiAnimalController::class, 'batch_events_create']);
Route::get('events/{id}', [ApiAnimalController::class, 'show']);
// Animal controler //

// ========== users starts ============== //
Route::POST('farms', [ApiFarmController::class, 'create']);
Route::get('farms/{id}', [ApiFarmController::class, 'show']);
Route::get('farms', [ApiFarmController::class, 'index']);
Route::get('my-drugs', [ApiFarmController::class, 'my_drugs']);
Route::get('locations', [ApiFarmController::class, 'locations']);


Route::get('users', [ApiUserController::class, 'index']);
Route::get('movements', [ApiMovement::class, 'index']);
Route::get('user-roles', [ApiMovement::class, 'user_roles']);
Route::get('movement-routes', [ApiMovement::class, 'routes']);
Route::get('system-users', [ApiMovement::class, 'system_users']);


Route::POST('transfer-animal/{id}', [ApiMovement::class, 'transfer_animal']);
Route::POST('checkpoint-session/{id}', [ApiMovement::class, 'checkpoint_session']);
Route::POST('checkpoint-verification', [ApiMovement::class, 'checkpoint_verification']);
Route::POST('slaughter-session', [ApiMovement::class, 'slaughter_session']);
Route::POST('movements-review/{id}', [ApiMovement::class, 'review']);
Route::POST('change-tag/{id}', [ApiAnimalController::class, 'change_tag']);
Route::POST('archive-animal/{id}', [ApiAnimalController::class, 'archive_animal']);
Route::POST('cancel-delete-request/{id}', [ApiAnimalController::class, 'cancel_delete_request']);

Route::POST('check-point-records', [ApiMovement::class, 'create_check_record']);
Route::get('check-point-records', [ApiMovement::class, 'get_check_record']);
Route::POST('movements', [ApiMovement::class, 'create']);
Route::POST('movements-v2', [ApiMovement::class, 'create_v2']);
Route::POST('trips-v2', [ApiMovement::class, 'trip_create_v2']);
Route::get('trips-v2', [ApiMovement::class, 'trips_v2']);
Route::get('movements/{id}', [ApiMovement::class, 'show']);
Route::POST('trip-records', [ApiMovement::class, 'create_trip_record']);
Route::POST('trip-end', [ApiMovement::class, 'trip_end']);
Route::POST('users', [ApiLoginController::class, 'create_account']);
// ========== users ends ============== //


// ========== lofin starts ============== //
Route::POST('login', [ApiLoginController::class, 'index']);
Route::POST('update-roles', [ApiLoginController::class, 'update_roles']);
Route::POST('remove-vet-role', [ApiLoginController::class, 'remove_vet_role']);
Route::POST('vet-profile', [ApiLoginController::class, 'vet_profile']);
Route::POST('update-profile', [ApiLoginController::class, 'update_profile']);
Route::get('me', [ApiLoginController::class, 'me']);
Route::POST('login/create-account', [ApiLoginController::class, 'create_account']);
// ========== lofin ends ============== //



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::POST("post-media-upload", [ApiAnimalController::class, 'upload_media']); //==>3<==*
Route::POST("product-create", [ApiShopController::class, "product_create"]); //==>2*
Route::get('products', [ApiShopController::class, 'products']); //==>1*
Route::post('products-delete', [ApiShopController::class, 'products_delete']); //==>4*
Route::post('chat-send', [ApiShopController::class, 'chat_send']); //==>5**
Route::post('chat-mark-as-read', [ApiShopController::class, 'chat_mark_as_read']); //==>8
Route::get('chat-heads', [ApiShopController::class, 'chat_heads']); //==>6**
Route::get('chat-messages', [ApiShopController::class, 'chat_messages']); //==>7**


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
        ->limit(50)->get();
    $data = [];
    foreach ($res_1 as $key => $v) {
        if ($v->parent != 0) {
            continue;
        }
        if ($v->details == 'Subcounty') {
            continue;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "$v->name - #" . $v->id
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
    )->orwhere(
        'phone_number',
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
            //continue;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "{$v->name} - {$v->phone_number} "
        ];
        $done_ids[] = $v->id;
    }

    return [
        'data' => $data
    ];
});
