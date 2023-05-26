<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\BatchSession;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Image;
use App\Models\Movement;
use App\Models\SlaughterRecord;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiAnimalController extends Controller
{


    public function upload_media(Request $request)
    {

        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        if (
            !isset($request->parent_id) ||
            $request->parent_id == null ||
            ((int)($request->parent_id)) < 1
        ) {

            return Utils::response([
                'status' => 0,
                'message' => "Local parent ID is missing.",
            ]);
        }


        if (
            !isset($request->parent_endpoint) ||
            $request->parent_endpoint == null ||
            (strlen(($request->parent_endpoint))) < 3
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Local parent ID endpoint is missing.",
            ]);
        }

        if (
            empty($_FILES)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Files not found.",
            ]);
        }

        $images = Utils::upload_images_1($_FILES, false);
        $_images = [];

        if (empty($images)) {
            return $this->error('Failed to upload files.');
        }

        $msg = "";
        foreach ($images as $src) {
            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  null;
            $img->parent_endpoint =  $request->parent_endpoint;
            $img->parent_id =  (int)($request->parent_id);
            $img->size = 0;
            $img->note = '';
            if (
                isset($request->note)
            ) {
                $img->note =  $request->note;
                $msg .= "Note not set. ";
            }

            $online_parent_id = ((int)($request->online_parent_id));
            if (
                $online_parent_id > 0
            ) {
                if (($request->parent_endpoint == 'animals-local')) {
                    $animal = Animal::find($online_parent_id);
                    if ($animal != null) {
                        $img->parent_endpoint =  'Animal';
                        $img->parent_id =  $animal->id;
                    } else {
                        $msg .= "parent_id NOT not found => {$request->online_parent_id}.";
                    }
                } else {
                    $msg .= "parent_endpoint NOT animals-local.";
                }
            } else {
                $msg .= "Online_parent_id NOT set. => {$online_parent_id} ";
            }

            $img->save();
            $_images[] = $img;
        }
        //Utils::process_images_in_backround();
        return Utils::response([
            'status' => 1,
            'data' => json_encode($_POST),
            'message' => "File uploaded successfully.",
        ]);
    }


    public function create_slaughter(Request $request)
    {

        if ($request->animal_ids == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animals must be provided.",
            ]);
        }



        $details =  ((string)($request->details));

        $user_id = Utils::get_user_id($request);

        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaugter house ID not found.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u  == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaughter house not found.",
            ]);
        }
        $animal = json_decode($request->animal_ids);

        if ($animal == null || empty($animal)) {
            return Utils::response([
                'status' => 0,
                'message' => "No animals found.",
            ]);
        }
        $i = 0;
        foreach ($animal as $key => $id) {
            $_id = ((int)($id));
            if ($_id < 1) {
                continue;
            }
            $an = Animal::find($_id);
            if ($an == null) {
                continue;
            }

            $sr = new SlaughterRecord();
            $sr->lhc = $an->lhc;
            $sr->v_id = $an->v_id;
            $sr->administrator_id = $user_id;
            $sr->e_id = $an->e_id;
            $sr->breed = $an->breed;
            $sr->sex = $an->sex;
            $sr->dob = $an->dob;
            $sr->fmd = $an->fmd;
            $sr->details = "Slautered by " . $u->name . ", ID " . $u->id . ". " . $details;
            $sr->destination_slaughter_house = $u->name;

            if ($sr->save()) {
                Utils::archive_animal([
                    'animal_id' => $_id,
                    'details' => $sr->details,
                    'event' => 'Slautered',
                ]);
            }
            $i++;
        }

        return Utils::response([
            'status' => 1,
            'message' => "{$i} Slauhter records have been created successfully.",
        ]);
    }



    public function archive_animal(Request $r, $id)
    {

        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $animal = Animal::find($id);
        if ($animal == null) {
            return Utils::response(['status' => 0, 'message' => "Animal was not found.",]);
        }


        $mgs = "{$animal->type} - {$animal->v_id} has been archived. Reason: {$r->reason}, {$r->details}. Open the App to see more details.";
        $title = "DELETED ANIMAL - {$animal->v_id}";


        if ($r->reason == null) {
            return Utils::response(['status' => 0, 'message' => "Reason is required.",]);
        }

        if ($r->details == null) {
            return Utils::response(['details' => 0, 'message' => "Details is required.",]);
        }


        Utils::archive_animal([
            'animal_id' => $animal->id,
            'reason' => $r->reason,
            'details' => $r->details,
        ]);

        Utils::sendNotification(
            $mgs,
            $u->id,
            $headings =  $title
        );


        return Utils::response([
            'status' => 1,
            'message' => $mgs,
        ]);
    }




    public function change_tag(Request $r, $id)
    {

        $animal = Animal::find($id);
        if ($animal == null) {
            return Utils::response(['status' => 0, 'message' => "Animal was not found.",]);
        }
        if ($r->new_v_id == null) {
            return Utils::response(['status' => 0, 'message' => "V-id is required.",]);
        }

        if ($r->new_e_id == null) {
            return Utils::response(['status' => 0, 'message' => "E-id is required.",]);
        }


        $an_1 = Animal::where('v_id', $r->new_v_id)->first();

        if ($an_1 != null) {
            return Utils::response(['status' => 0, 'message' => "Animal with same V-id already exist.",]);
        }

        $an_1 = Animal::where('e_id', $r->new_e_id)->first();
        if ($an_1 != null) {
            return Utils::response(['status' => 0, 'message' => "Animal with same E-id already exist.",]);
        }

        $animal->v_id = $r->new_v_id;
        $animal->e_id = $r->new_e_id;
        $animal->save();

        return Utils::response([
            'status' => 1,
            'message' => "Animal's E-ID and V-ID was changed successfully.",
        ]);
    }


    public function create_sale(Request $request)
    {
        if ($request->animal_ids == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animals must be provided.",
            ]);
        }

        if ($request->trader == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Trader ID must be provided.",
            ]);
        }

        $trader =  ((int)($request->trader));
        if ($trader < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Trader ID not found.",
            ]);
        }
        $t = Administrator::find($trader);
        if ($t  == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Trader not found.",
            ]);
        }
        $animal = json_decode($request->animal_ids);

        if ($animal == null || empty($animal)) {
            return Utils::response([
                'status' => 0,
                'message' => "No animals found.",
            ]);
        }
        $i = 0;
        foreach ($animal as $key => $id) {
            $_id = ((int)($id));
            if ($_id < 1) {
                continue;
            }
            $an = Animal::find($_id);
            if ($an == null) {
                continue;
            }
            $i++;
            $an->trader = $trader;
            $an->save();
        }

        return Utils::response([
            'status' => 1,
            'message' => "{$i} Animals were assigned to trader successfully.",
        ]);
    }


    public function store_batch_event(Request $r)
    {
        $user_id = Utils::get_user_id($r);

        if (

            $r->name == null ||
            $r->session_date == null ||
            $r->type == null ||
            $user_id == null ||
            $r->items == null
        ) {
            return Utils::response([
                'status' => 2,
                'message' => "Some parameters missing.",
            ]);
        }

        $exist = BatchSession::where([
            'session_date' => $r->session_date,
            'administrator_id' => $user_id
        ])->first();

        if ($exist != null) {
            return Utils::response([
                'status' => 1,
                'message' => "Events already created.",
                'data' => null
            ]);
        }

        $items = json_decode($r->items);
        $date = Carbon::parse($r->session_date);
        $type = "";
        if ($r->type == 'Roll call') {
            $type = 'Roll call';
        } else if ($r->type == 'Treatment') {
            $type = 'Treatment';
        } else if ($r->type == 'Milk') {
            $type = 'Milking';
        }


        if ($r->type == 'Milk') {



            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->session_date = $r->session_date;
            $session->type = 'Milking';
            $session->description = "Milked animals";
            $session->save();
            $animal_ids_found = [];
            $litters = 0;


            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->animal_id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->milk =  $v->milk;
                $ev->type = 'Milking';
                $ev->is_batch_import =  0;
                $ev->detail =  "$ev->milk litteres milked from $ev->v_id";
                $ev->description =  $ev->detail;
                $ev->short_description =  $ev->detail;
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
                $litters += ((int)($ev->milk));
            }

            $num = count($animal_ids_found);
            $session->description =    "Milked {$litters} litters from {$num} animals in a {$session->name} session. Open the App to see details.";
            $session->save();
            Utils::sendNotification(
                $session->description,
                $session->administrator_id,
                $headings = "Milked {$num} animals."
            );
        }




        if ($r->type == 'Treatment') {
            $meds =   [];

            try {
                $meds = json_decode($r->drugItem);
            } catch (\Throwable $th) {
                $meds = [];
            }

            if (
                $r->drugItem == null
            ) {
                return Utils::response([
                    'status' => 2,
                    'message' => "Drugs   missing.",
                ]);
            }

            $meds_text = "";

            foreach ($meds as $m) {
                $meds_text .= "$m->name: $m->quantity units, ";
                $d = DrugStockBatch::find(((int)($m->id)));
                if ($d == null) {
                    continue;
                }
                $d->current_quantity = $d->current_quantity - ((int)($m->quantity));
                $d->save();
            }
            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->type = $r->type;
            $session->session_date = $r->session_date;
            $session->description = "Treated animals with  $meds_text.";
            $session->save();
            $animal_ids_found = [];


            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->animal_id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->type = 'Batch Treatment';
                $ev->is_batch_import =  0;
                $ev->detail =  "$meds_text was applied to this animal.";
                $ev->description =  "$meds_text was applied to this animal.";
                $ev->short_description =  "Treatment - {$meds_text}.";
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
            }

            $num = count($animal_ids_found);

            Utils::sendNotification(
                "Treated {$num} animals with  {$meds_text} in a {$session->name} session. Open the App to see details.",
                $session->administrator_id,
                $headings = $session->name . ' - Batch treatment'
            );
        }


        if ($r->type == 'Roll call') {

            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->type = $r->type;
            $session->session_date = $r->session_date;
            $session->session_category = $r->session_category;
            $session->description = $r->description;
            $session->save();
            $animal_ids_found = [];


            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->animal_id)),
                    'type' => $r->session_category,
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                if ($r->type == 'Roll call') {
                    $ev = new Event();
                    $ev->created_at =  $date;
                    $ev->updated_at =  $date;
                    $ev->time_stamp =  $date;
                    $ev->administrator_id =  $an->administrator_id;
                    $ev->animal_id =  $an->id;
                    $ev->e_id =  $an->e_id;
                    $ev->v_id =  $an->v_id;
                    $ev->type =  $type;
                    $ev->is_batch_import =  0;
                    $ev->detail =  "Present in Roll-call - {$r->name}.";
                    $ev->description =  "Present in Roll-call - {$r->name}.";
                    $ev->short_description =  "Roll-call - {$r->name}.";
                    $ev->session_id =  $session->id;
                    $ev->is_present =  1;
                    $ev->save();
                }
            }


            $absent = 0;
            foreach (Animal::where([
                'administrator_id' => $user_id,
                'type' => $r->session_category,
            ])->get() as $an) {
                if (in_array($an->id, $animal_ids_found)) {
                    continue;
                }
                $absent++;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->type =  $type;
                $ev->is_batch_import =  0;
                $ev->detail =  "Absent from Roll-call - {$r->name}.";
                $ev->description =  "Absent from Roll-call - {$r->name}.";
                $ev->short_description =  "Roll-call - {$r->name}.";
                $ev->session_id =  $session->id;
                $ev->is_present =  0;
                $ev->save();
            }

            $session->present = count($animal_ids_found);
            $session->absent =  $absent;
            $session->save();

            Utils::sendNotification(
                "{$session->name}. Animals present: {$session->present}, Animals absent: {$session->absent}. Open the App to see full list.",
                $session->administrator_id,
                $headings = $r->session_category . ' Roll-call'
            );
        }



        return Utils::response([
            'status' => 1,
            'message' => "Events were created successfully.",
            'data' => null
        ]);
    }



    public function batch_events_create(Request $r)
    {

        $user_id = Utils::get_user_id($r);

        if (

            $r->name == null ||
            $r->date_time == null ||
            $r->type == null ||
            $user_id == null ||
            $r->details == null
        ) {
            return Utils::response([
                'status' => 2,
                'message' => "Some parameters missing.",
            ]);
        }


        $exist = BatchSession::where([
            'session_date' => $r->date_time,
            'administrator_id' => $user_id
        ])->first();

        if ($exist != null) {
            return Utils::response([
                'status' => 1,
                'message' => "Events already created.",
                'data' => null
            ]);
        }

        $items = json_decode($r->details);

        $date = Carbon::parse($r->date_time);
        $type = "";
        if ($r->type == 'Roll call') {
            $type = 'Roll call';
        } else if ($r->type == 'Treatment') {
            $type = 'Treatment';
        } else if ($r->type == 'Milking') {
            $type = 'Milking';
        }



        if ($r->type == 'Milking') {


            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->session_date = $r->date_time;
            $session->type = 'Milking';
            $session->description = "Milked animals";
            $session->save();
            $animal_ids_found = [];
            $litters = 0;


            foreach ($items as $v) {

                $an = Animal::where([
                    'id' => ((int)($v->id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->milk =  $v->milk;
                $ev->type = 'Milking';
                $ev->is_batch_import =  0;
                $ev->detail =  "$ev->milk litteres milked from $ev->v_id";
                $ev->description =  $ev->detail;
                $ev->short_description =  $ev->detail;
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
                $litters += ((int)($ev->milk));
            }

            $num = count($animal_ids_found);
            $session->description =    "Milked {$litters} litters from {$num} animals in a {$session->name} session. Open the App to see details.";
            $session->save();
            Utils::sendNotification(
                $session->description,
                $session->administrator_id,
                $headings = "Milked {$num} animals."
            );
        } else if ($r->type == 'Treatment') {

            $meds =   [];

            if (
                $r->drugs == null
            ) {
                return Utils::response([
                    'status' => 2,
                    'message' => "Drugs   missing.",
                ]);
            }

            try {
                $meds = json_decode($r->drugs);
            } catch (\Throwable $th) {
                $meds = [];
            }

            $meds_text = "";

            foreach ($meds as $m) {
                $meds_text .= "$m->drug_text: $m->quantity units, ";

                $d = DrugStockBatch::find(((int)($m->drug_id)));
                if ($d == null) {
                    continue;
                }
                $d->current_quantity = $d->current_quantity - ((int)($m->quantity));
                $d->save();
            }


            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->type = $r->type;
            $session->session_date = $r->date_time;
            $session->description = "Treated animals with  $meds_text.";
            $session->save();
            $animal_ids_found = [];


            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->type = 'Batch Treatment';
                $ev->is_batch_import =  0;
                $ev->detail =  "$meds_text was applied to this animal.";
                $ev->description =  "$meds_text was applied to this animal.";
                $ev->short_description =  "Treatment - {$meds_text}.";
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
            }

            $num = count($animal_ids_found);

            Utils::sendNotification(
                "Treated {$num} animals with  {$meds_text} in a {$session->name} session. Open the App to see details.",
                $session->administrator_id,
                $headings = $session->name . ' - Batch treatment'
            );
        } else if ($r->type == 'Roll call') {

            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->type = $r->type;
            $session->session_date = $r->session_date;
            $session->session_category = $r->session_category;
            $session->description = $r->description;
            $session->save();
            $animal_ids_found = [];


            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->animal_id)),
                    'type' => $r->session_category,
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                if ($r->type == 'Roll call') {
                    $ev = new Event();
                    $ev->created_at =  $date;
                    $ev->updated_at =  $date;
                    $ev->time_stamp =  $date;
                    $ev->administrator_id =  $an->administrator_id;
                    $ev->animal_id =  $an->id;
                    $ev->e_id =  $an->e_id;
                    $ev->v_id =  $an->v_id;
                    $ev->type =  $type;
                    $ev->is_batch_import =  0;
                    $ev->detail =  "Present in Roll-call - {$r->name}.";
                    $ev->description =  "Present in Roll-call - {$r->name}.";
                    $ev->short_description =  "Roll-call - {$r->name}.";
                    $ev->session_id =  $session->id;
                    $ev->is_present =  1;
                    $ev->save();
                }
            }


            $absent = 0;
            foreach (Animal::where([
                'administrator_id' => $user_id,
                'type' => $r->session_category,
            ])->get() as $an) {
                if (in_array($an->id, $animal_ids_found)) {
                    continue;
                }
                $absent++;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->type =  $type;
                $ev->is_batch_import =  0;
                $ev->detail =  "Absent from Roll-call - {$r->name}.";
                $ev->description =  "Absent from Roll-call - {$r->name}.";
                $ev->short_description =  "Roll-call - {$r->name}.";
                $ev->session_id =  $session->id;
                $ev->is_present =  0;
                $ev->save();
            }

            $session->present = count($animal_ids_found);
            $session->absent =  $absent;
            $session->save();

            Utils::sendNotification(
                "{$session->name}. Animals present: {$session->present}, Animals absent: {$session->absent}. Open the App to see full list.",
                $session->administrator_id,
                $headings = $r->session_category . ' Roll-call'
            );
        }



        return Utils::response([
            'status' => 1,
            'message' => "Events were created successfully.",
            'data' => null
        ]);
    }


    public function store_event(Request $request)
    {

        $user_id = Utils::get_user_id($request);


        if ($request->session_id != null) {
            if (strlen($request->session_id) > 3) {
                $e =  Event::where([
                    'session_id' => $request->session_id,
                    'administrator_id' => $user_id
                ])->first();
                if ($e != null) {
                    return Utils::response([
                        'status' => 1,
                        'message' => "This event is a duplicate.",
                        'data' => null
                    ]);
                }
            }
        }


        if ($request->animal_id == null) {
            return Utils::response([
                'status' => 2,
                'message' => "Animal ID must be provided.",
            ]);
        }

        if ($request->type == null) {
            return Utils::response([
                'status' => 2,
                'message' => "Event type must be provided.",
            ]);
        }

        $animal = Animal::find(((int)($request->animal_id)));
        if ($animal == null) {
            return Utils::response([
                'status' => 2,
                'message' => "Animal not found on our database.",
            ]);
        }

        if ($request->type == 'Milking') {
            $milk = ((int)($request->milk));
            if ($milk < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Enter valid milk parameters.",
                ]);
            }
        }


        if (!isset($request->session_id)) {
            return Utils::response([
                'status' => 0,
                'message' => "Session not set.",
            ]);
        }
        $session_id = trim($request->session_id);

        $ev = Event::where([
            'session_id' => $session_id,
            'animal_id' => $request->animal_id,
        ])->first();

        if ($ev != null) {
            return Utils::response([
                'status' => 1,
                'message' => "Duplicate of event is detected.",
            ]);
        }

        $event = new Event();
        $event->animal_id = (int)($request->animal_id);


        $event->detail = $request->detail;
        $event->session_id = $session_id;
        $event->sub_county_id = $request->sub_county_id;
        $event->farm_id = $request->farm_id;
        $event->animal_id = $request->animal_id;
        $event->type = $request->type;
        $event->approved_by = $request->approved_by;
        $event->animal_type = $request->animal_type;
        $event->vaccine_id = $request->vaccine_id;
        $event->medicine_id = $request->medicine_id;
        $event->medicine_quantity = $request->medicine_quantity;
        $event->vaccination = $request->vaccination;
        $event->time_stamp = $request->time_stamp;
        $event->import_file = $request->import_file;
        $event->description = $request->description;
        $event->temperature = $request->temperature;
        $event->e_id = $request->e_id;
        $event->v_id = $request->v_id;
        $event->pregnancy_check_method = $request->pregnancy_check_method;
        $event->pregnancy_check_results = $request->pregnancy_check_results;
        $event->pregnancy_expected_sex = $request->pregnancy_expected_sex;
        $event->pregnancy_fertilization_method = $request->pregnancy_fertilization_method;
        $event->disease_test_results = $request->disease_test_results;
        $event->disease_id = $request->disease_id;
        $event->milk = $request->milk;
        $event->weight = $request->weight;



        try {
            $event->save();
            return Utils::response([
                'status' => 1,
                'message' => "Event was created successfully.",
                'data' => $event
            ]);
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 2,
                'message' => "Failed -  $th",
            ]);
        }


        return Utils::response([
            'status' => 0,
            'message' => "Failed to save event on database.",
        ]);
    }



    public function photo_downloads(Request $request)
    {

        $user_id = Utils::get_user_id($request);
        $data = [];

        foreach (Animal::where([
            'administrator_id' => $user_id
        ])
            ->orderBy('id', 'desc')
            ->limit(1000)
            ->get() as $animal) {

            foreach ($animal->photos as $key => $pic) {
                $path = $_SERVER['DOCUMENT_ROOT'] . "/public/storage/images/" . $pic->src;
                if (!file_exists($path)) {
                    //  $pic->delete();
                    continue;
                }

                unset($pic->updated_at);
                unset($pic->administrator_id);
                unset($pic->thumbnail);
                unset($pic->size);
                unset($pic->deleted_at);
                unset($pic->type);
                unset($pic->product_id);
                unset($pic->parent_endpoint);
                $data[] = $pic;
            }



            //  $data[] = $animal->photos;
        }

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);
    }

    public function index(Request $request)
    {

        $user_id = Utils::get_user_id($request);
        $data = [];

        foreach (Animal::where([
            'administrator_id' => $user_id
        ])
            ->orderBy('id', 'desc')
            ->limit(1000)
            ->get() as $animal) {
            $animal->district_text = "-";
            if ($animal->district != null) {
                $animal->district_text = $animal->district->name_text;
            }
            if ($animal->sub_county != null) {
                $animal->sub_county_text = $animal->sub_county->name_text;
            }

            $x = $animal;


            $data[] = $x;
        }

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);


        $user_role = Utils::is_admin($request);


        //$user_id = Utils::get_user_id($request);
        $user_id = $administrator_id;
        $u = Administrator::find($administrator_id);
        $role = Utils::get_role($u);

        $s = $request->s;
        $items = [];
        $_items = [];

        if ($s != null) {
            if (strlen($s) > 0) {

                $f = Farm::where("holding_code", $s)->first();
                if ($f != null) {
                    $items = $f->animals;
                }

                $_items = Animal::where(
                    'e_id',
                    'like',
                    '%' . trim($request->s) . '%',
                )->paginate(100000000)->withQueryString()->items();

                $__items = Animal::where(
                    'v_id',
                    'like',
                    '%' . trim($request->s) . '%',
                )->paginate(100000000)->withQueryString()->items();

                $items_ids = [];
                $___items = [];

                foreach ($items as $key => $v) {
                    if (!in_array($v->id, $items_ids)) {
                        $items_ids[] = $v->id;
                        $___items[] = $v;
                    }
                }

                foreach ($_items as $key => $v) {
                    if (!in_array($v->id, $items_ids)) {
                        $items_ids[] = $v->id;
                        $___items[] = $v;
                    }
                }

                foreach ($__items as $key => $v) {
                    if (!in_array($v->id, $items_ids)) {
                        $items_ids[] = $v->id;
                        $___items[] = $v;
                    }
                }


                return $___items;
            }
        }

        if (empty($items)) {
            $per_page = 100000000;
            if (isset($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($role == 'slaughter') {
                $moves = Movement::where('destination_slaughter_house', '=', $user_id)->where('status', '=', 'Approved')->get();
                foreach ($moves as $key => $value) {
                    if ($value->movement_has_movement_animals != null) {
                        foreach ($value->movement_has_movement_animals as $_value) {
                            if ($_value->movement_animal_id != null) {
                                $__an = Animal::find($_value->movement_animal_id);
                                if ($__an != null) {
                                    $items[] = $__an;
                                }
                            }
                        }
                    }
                }
            } else {
                $items = Animal::paginate($per_page)->withQueryString()->items();
            }
        }

        foreach ($items as $key => $value) {
            if ($role == 'farmer') {
                if ($value->administrator_id != $administrator_id) {
                    continue;
                }
            } else if ($role == 'trader') {
                if ($u->id != $value->trader) {
                    continue;
                }
            } else if ($role == 'dvo') {
                if ($u->dvo != $value->district_id) {
                    continue;
                }
            }

            $items[$key]->owner_name  = "";
            if ($items[$key]->farm != null) {
                if ($items[$key]->farm->user != null) {
                    $items[$key]->owner_name = $items[$key]->farm->user->name;
                }
            }

            $items[$key]->owner_name = "";
            $items[$key]->district_name = "";
            $items[$key]->created = Carbon::parse($value->created)->toFormattedDateString();
            if ($value->district != null) {
                $items[$key]->district_name = $value->district->name;
            }
            if ($value->sub_county != null) {
                $items[$key]->sub_county_name = $value->sub_county->name;
            }
            unset($items[$key]->farm);
            unset($items[$key]->district);
            unset($items[$key]->sub_county);
            $_items[] = $items[$key];
        }
        return $_items;
    }

    public function index_v2(Request $request)
    {

        $user_id = Utils::get_user_id($request);
        

        $query = Animal::where([
            'administrator_id' => $user_id
        ])
            ->orderBy('id', 'desc')
            ->limit(1000);

        if ($request->updated_at != null) {
            $query->whereDate('updated_at', '>', Carbon::parse($request->updated_at));
        } 
        $ans = $query->get();   
        $data = [];
        foreach ($ans as $key => $v) {
            unset($v->images);
            unset($v->photos);
            unset($v->district);
            unset($v->sub_county);
            $data[] = $v;
        }
        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]); 
    }



    public function slaughters(Request $request)
    {
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }

        $items = [];
        $_items = [];

        $items = SlaughterRecord::where('administrator_id', $user_id)->get();
        foreach ($items as $key => $value) {
            $value->created = Carbon::parse($value->created_at)->toFormattedDateString();
            $_items[] = $value;
        }
        return $_items;
    }


    public function show($id)
    {
        $item = Animal::find($id);

        $item->owner_name  = "";
        if ($item->farm != null) {
            if ($item->farm->user != null) {
                $item->owner_name = $item->farm->user->name;
            }
        }

        $item->owner_name = "";
        $item->district_name = "";
        $item->created = Carbon::parse($item->created)->toFormattedDateString();
        if ($item->district != null) {
            $item->district_name = $item->district->name;
        }
        if ($item->sub_county != null) {
            $item->sub_county_name = $item->sub_county->name;
        }
        unset($item->farm);
        unset($item->district);
        unset($item->sub_county);


        return $item;
    }






    public function store(Request $request)
    {
        return Administrator::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $Administrator = Administrator::findOrFail($id);
        $Administrator->update($request->all());

        return $Administrator;
    }

    public function delete(Request $request, $id)
    {
        $Administrator = Administrator::findOrFail($id);
        $Administrator->delete();

        return 204;
    }

    public function create(Request $request)
    {
        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        if (
            !isset($request->farm_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide farm."
            ]);
        }

        if (isset($request->has_no_tag)) {
            $has_no_tag = ((int)($request->has_no_tag));
            if ($has_no_tag == 1) {
                $p = Animal::find($request->parent_id);
                if ($p == null) {
                    return Utils::response([
                        'status' => 0,
                        'message' => "Parent animal not found."
                    ]);
                }

                $count = Animal::where([
                    'parent_id' => $p->id
                ])->count();
                $count++;
                $request->e_id = "temp-{$p->e_id}-{$count}";
                $request->v_id = "temp-{$p->v_id}-{$count}";
            }
        }

        if (
            !isset($request->e_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide e_id."
            ]);
        }

        if (
            !isset($request->type)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide type."
            ]);
        }

        if (
            !isset($request->e_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide e_id."
            ]);
        }

        if (
            !isset($request->breed)
        ) {
            $request->breed = 'Other';
            /* return Utils::response([
                'status' => 0,
                'message' => "You must provide breed."
            ]); */
        }


        $animal = Animal::where('e_id', $request->e_id)->first();
        if ($animal != null) {
            return Utils::response([
                'status' => 2,
                'message' => "Animal with same E-ID already exist in the system."
            ]);
        }

        if (
            !isset($request->sex)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide sex."
            ]);
        }



        $f = new Animal();
        $f->e_id = $request->e_id;
        $f->farm_id = $request->farm_id;
        $f->type = $request->type;
        $f->v_id = $request->v_id;
        $f->lhc = $request->lhc;
        $f->breed = $request->breed;
        $f->sex = $request->sex;
        $f->dob = $request->dob;
        $f->fmd = $request->fmd;
        $f->stage = $request->stage;
        $f->parent_id = $request->parent_id;
        $f->status = 'Active';
        $f->save();



        if (isset($request->local_id)) {
            $local_id = (int)($request->local_id);

            $imgs = Image::where([
                'administrator_id' => $administrator_id,
                'parent_id' => $local_id,
                'parent_endpoint' => 'animals-local',
            ])->get();

            foreach ($imgs as  $img) {
                $img->parent_id = $f->id;
                $img->parent_endpoint = 'Animal';
                $img->save();
            }
        }



        return Utils::response([
            'status' => 1,
            'message' => "Animal created successfully.",
            'data' => $f
        ]);
    }

    public function events(Request $request)
    {

        $user_id = Utils::get_user_id($request);

        $data = Event::where([
            'administrator_id' => $user_id
        ])->get();

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);


        $per_page = 10000000;
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
        }

        $administrator_id = Utils::get_user_id($request);
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }
        $role = Utils::get_role($u);

        $is_search = false;
        $items = [];
        $s = $request->s;
        if ($s != null) {
            if (strlen($s) > 0) {
                $is_search = true;

                $an = Animal::where("e_id", $s)->first();
                if ($an == null) {
                    $an = Animal::where("v_id", $s)->first();
                }
                if ($an == null) {
                    return [];
                }
                if (!isset($an->id)) {
                    return [];
                }

                $items = Event::where("animal_id", $an->id)->get();
                if (empty($items)) {
                    return [];
                }
            }
        }

        if (!$is_search) {
            $items = Event::paginate($per_page)->withQueryString()->items();
        }


        $_items = [];
        foreach ($items as $key => $value) {

            if ($role == 'farmer') {
                if ($value->administrator_id != $administrator_id) {
                    continue;
                }
            } else if ($role == 'scvo') {
                if ($u->scvo != $value->sub_county_id) {
                    continue;
                }
            }

            $items[$key]->e_id  = "";
            $items[$key]->v_id  = "";
            $items[$key]->lhc  = "";
            if ($items[$key]->animal != null) {
                if ($items[$key]->animal->e_id != null) {
                    $items[$key]->e_id  = $items[$key]->animal->e_id;
                    $items[$key]->v_id  = $items[$key]->animal->v_id;
                    $items[$key]->lhc  = $items[$key]->animal->lhc;
                }
                unset($items[$key]->animal);
            }
            $items[$key]->created = Carbon::parse($value->created)->toFormattedDateString();
            $_items[] = $items[$key];
        }
        return $_items;
    }
}
