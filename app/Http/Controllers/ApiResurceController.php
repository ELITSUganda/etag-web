<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\ArchivedAnimal;
use App\Models\BatchSession;
use App\Models\District;
use App\Models\DrugDosage;
use App\Models\DrugDosageItem;
use App\Models\Event;
use App\Models\Group;
use App\Models\Utils;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vaccine;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Middleware\Session;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApiResurceController extends Controller
{



    public function dialy_milk_records(Request $r)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $data = [];
        $records = [];
        $prev = 0;
        $id = 0;
        for ($i = 29; $i >= -1; $i--) {
            $min = new Carbon();
            $max = new Carbon();
            $max->subDays($i);
            $min->subDays(($i + 1));
            $id++;

            $max = Carbon::parse($max->format('Y-m-d'));
            $min = Carbon::parse($min->format('Y-m-d'));


            $milk = Event::whereBetween('created_at', [$min, $max])
                ->where([
                    'type' => 'Milking',
                    'administrator_id' => $administrator_id,
                ])
                ->sum('milk');

            $count = Event::whereBetween('created_at', [$min, $max])
                ->where([
                    'type' => 'Milking',
                    'administrator_id' => $administrator_id,
                ])
                ->count('animal_id');




            $rec['day'] = Utils::my_date_1($min);
            $rec['animals'] = $count;
            $rec['milk'] = $milk;
            $rec['id'] = $id;

            $rec['progress'] = 0;
            if ($count > 0) {
                $avg = $milk / $count;
                $rec['progress'] =  $avg - $prev;
                $rec['progress'] = round($rec['progress'], 2);
                $prev = $avg;
            }

            $data['records'][] = $rec;
        }

        $data['records'] = array_reverse($data['records']);

        return Utils::response([
            'status' => 1,
            'data' => $data['records'],
            'message' => 'Success'
        ]);
    }


    public function roll_call(Request $r)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $data = [];
        foreach (BatchSession::where([
            'administrator_id' => $administrator_id,
            'type' => 'Roll call'
        ])->get() as $session) {
            $events = [];
            foreach (Event::where([
                'session_id' => $session->id
            ])->get() as $eve) {
                $event['animal_id'] = $eve->animal_id;
                $event['e_id'] = $eve->e_id;
                $event['v_id'] = $eve->v_id;
                $event['is_present'] = $eve->is_present;
                if ($eve->animal != null) {
                    $event['animal_photo'] = url($eve->animal->photo);
                } else {
                    $event['animal_photo'] = "";
                }

                $events[] = $event;
            }
            $session->events = json_encode($events);
            $data[] = $session;
        }



        return Utils::response([
            'status' => 1,
            'data' => $data,
            'message' => 'Success'
        ]);
    }

    public function archived_animals(Request $r)
    {

        $worker_id = Utils::get_user_id($r);
        $worker = User::find($worker_id);
        if ($worker != null) {
            return Utils::response([
                'status' => 0,
                'message' => "Worker not allowed to delete animals.",
            ]);
        }

        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }
        Utils::systemBoot($u);
        $data = ArchivedAnimal::where([
            'administrator_id' => $administrator_id,
        ])->get();

        return Utils::response([
            'status' => 1,
            'data' => $data,
            'message' => 'Success'
        ]);
    }

    public function groups(Request $r)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }
        Utils::systemBoot($u);
        //get groups for this user using the group in models
        $data = Group::where([
            'administrator_id' => $administrator_id,
        ])->get();
        return Utils::response([
            'status' => 1,
            'data' => $data,
            'message' => 'Success'
        ]);
    }

    public function manifest(Request $r)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $max = new Carbon();
        $min_week = new Carbon();
        $min_prev_week = new Carbon();
        $min_prev_month = new Carbon();
        $min_this_month = new Carbon();
        $min_this_year = new Carbon();
        $min_week->subDays(7);
        $min_prev_week->subDays(14);
        $min_this_month->subDays(30);
        $min_prev_month->subDays(60);
        $min_this_year->subDays(365);

        $max = Carbon::parse($max->format('Y-m-d'));
        $min_week = Carbon::parse($min_week->format('Y-m-d'));
        $min_prev_week = Carbon::parse($min_prev_week->format('Y-m-d'));
        $min_this_month = Carbon::parse($min_this_month->format('Y-m-d'));
        $min_prev_month = Carbon::parse($min_prev_month->format('Y-m-d'));
        $min_this_year = Carbon::parse($min_this_year->format('Y-m-d'));
        $manifest['id'] = 1;
        $manifest['milk_price'] = $u->milk_price;


        $manifest['milk_this_week_quantity'] = Event::whereBetween('created_at', [$min_week, $max])
            ->where([
                'type' => 'Milking',
                'administrator_id' => $administrator_id,
            ])
            ->sum('milk');

        $manifest['milk_prev_week_quantity'] = Event::whereBetween('created_at', [$min_prev_week, $min_week])
            ->where([
                'type' => 'Milking',
                'administrator_id' => $administrator_id,
            ])
            ->sum('milk');

        $manifest['milk_this_month_quantity'] = Event::whereBetween('created_at', [$min_this_month, $max])
            ->where([
                'type' => 'Milking',
                'administrator_id' => $administrator_id,
            ])
            ->sum('milk');

        $manifest['milk_prev_month_quantity'] = Event::whereBetween('created_at', [$min_prev_month, $min_this_month])
            ->where([
                'type' => 'Milking',
                'administrator_id' => $administrator_id,
            ])
            ->sum('milk');

        $manifest['milk_this_year_quantity'] = Event::whereBetween('created_at', [$min_this_year, $max])
            ->where([
                'type' => 'Milking',
                'administrator_id' => $administrator_id,
            ])
            ->sum('milk');

        $tot_milk = Event::where([
            'type' => 'Milking',
            'administrator_id' => $administrator_id,
        ])
            ->sum('milk');

        $tot_pros = Event::where([
            'type' => 'Milking',
            'administrator_id' => $administrator_id,
        ])
            ->count('id');


        try {
            if ($tot_pros > 0) {
                $manifest['average_production'] = round(($tot_milk / $tot_pros), 2);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        $manifest['last_update'] = Utils::my_date_time(Carbon::now());
        $manifest['app_vision'] = 34;

        $data[] = $manifest;
        return Utils::response([
            'status' => 1,
            'data' => $data,
            'message' => 'Success'
        ]);
        return 'manifest';
    }




    public function index(Request $r, $model)
    {

        $className = "App\Models\\" . $model;
        $obj = new $className;

        if (isset($_POST['_method'])) {
            unset($_POST['_method']);
        }
        if (isset($_GET['_method'])) {
            unset($_GET['_method']);
        }

        $conditions = [];
        foreach ($_GET as $k => $v) {
            if (substr($k, 0, 2) == 'q_') {
                $conditions[substr($k, 2, strlen($k))] = trim($v);
            }
        }
        $is_private = true;
        if (isset($_GET['is_not_private'])) {
            $is_not_private = ((int)($_GET['is_not_private']));
            if ($is_not_private == 1) {
                $is_private = false;
            }
        }
        if ($is_private) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);

            if ($u == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "User not found.",
                ]);
            }
            $conditions['administrator_id'] = $administrator_id;
        }

        $items = [];
        $msg = "";

        try {
            $items = $className::where($conditions)->get();
            $msg = "Success";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $items,
                'message' => 'Success'
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }



    public function send_verification_code(Request $r)
    {
        if (!isset($r->phone)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Phone number is required.'
            ]);
        }
        $phone = Utils::prepare_phone_number($r->phone);
        if (!Utils::phone_number_is_valid($phone)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Invalid phone number.'
            ]);
        }
        $code = rand(1000, 9999) . "";
        $msg = $code . " is your U-LITS verification code.";
        $resp = Utils::send_message($phone, $msg);
        if ($resp != '') {
            //failed
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to send verification code because ' . $resp
            ]);
        }
        return Utils::response([
            'data' => $code,
            'status' => 1,
            'code' => 1,
            'message' => 'Verification code sent successfully.'
        ]);
    }
    public function save_new_drug_dosage(Request $r)
    {
        $_items = $r->items;
        if ($_items == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'No item found.'
            ]);
        }
        $items = json_decode($_items);
        if ((!is_array($items)) || empty($items)) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'No presecription item found.'
            ]);
        }

        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $obj = new DrugDosage();
        $cols = Utils::getTableColumns($obj);
        foreach ($_POST as $key => $value) {
            if (!in_array($key, $cols,)) {
                continue;
            }
            $obj->$key = $value;
        }
        $obj->administrator_id = $u->id;

        $success = false;
        $msg = "";

        try {
            $obj->save();
            $msg = "Successfully saved.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        if ($success) {
            foreach ($items as $key => $item) {
                $times_days = ((int)($item->times_days));
                for ($j = 0; $j < $times_days; $j++) {
                    $i = new DrugDosageItem();
                    $i->drug_name = $item->drug_name;
                    $i->drug_dosage_id = $obj->id;
                    $i->quantity = $item->quantity;
                    $i->times_days = $item->times_days;
                    $i->status = 0;
                    $i->date_to_be_done = Carbon::now();
                    $i->date_to_be_done->addDay($j);
                    $i->times_per_day = $item->times_per_day;
                    $i->administrator_id = $obj->administrator_id;
                    $i->rout = ($j + 1);
                    $i->save();
                }
            }
        }

        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }
    public function store(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        if (isset($_POST['_method'])) {
            unset($_POST['_method']);
        }
        if (isset($_POST['online_id'])) {
            unset($_POST['online_id']);
        }

        $className = "App\Models\\" . $model;
        $obj = new $className;
        $cols = Utils::getTableColumns($obj);




        $is_private = true;
        if (isset($_POST['is_not_private'])) {
            $is_not_private = ((int)($_POST['is_not_private']));
            if ($is_not_private == 1) {
                $is_private = false;
            }

            unset($_POST['is_not_private']);
        }
        if ($is_private) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);

            if ($u == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "User not found.",
                ]);
            }
            $_POST['administrator_id'] = $administrator_id;
        }




        foreach ($_POST as $key => $value) {
            if (!in_array($key, $cols,)) {
                continue;
            }
            $obj->$key = $value;
        }

        $success = false;
        $msg = "";

        try {
            $obj->save();
            $msg = "Successfully saved.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }


    public function delete(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item already deleted.",
            ]);
        }


        try {
            $obj->delete();
            $msg = "Deleted successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }


    public function update(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item not found.",
            ]);
        }


        unset($_POST['_method']);
        if (isset($_POST['online_id'])) {
            unset($_POST['online_id']);
        }
        if (isset($_POST['temp_worker_id'])) {
            unset($_POST['temp_worker_id']);
        }
        if (isset($_POST['user_id'])) {
            unset($_POST['user_id']);
        }
        if (isset($_POST['User-id'])) {
            unset($_POST['User-id']);
        }
        if (isset($_POST['user'])) {
            unset($_POST['user']);
        }
        if (isset($_POST['user'])) {
            unset($_POST['user']);
        }
        if (isset($_POST['User-Id'])) {
            unset($_POST['User-Id']);
        }

        if (isset($_POST['temp_worker_id'])) {
            unset($_POST['temp_worker_id']);
        } 

        foreach ($_POST as $key => $value) {
            $obj->$key = $value;
        }


        $success = false;
        $msg = "";
        try {
            $obj->save();
            $msg = "Updated successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }
}
