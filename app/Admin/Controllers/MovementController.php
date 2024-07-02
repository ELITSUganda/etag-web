<?php

namespace App\Admin\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Movement;
use App\Models\MovementAnimal;
use App\Models\MovementHasMovementAnimal;
use App\Models\SlaughterHouse;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\NestedForm;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LMPR - (Movement Permits)';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {




        $grid = new Grid(new Movement());

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableBatchActions();

        if (Admin::user()->isRole('slaughter')) {

            $u = Auth::user();
            $recs = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 5])->get();
            $ids = [];
            foreach ($recs as $rec) {
                $ids[] = $rec->type_id;
            }

            $grid->model()
                ->where('destination_slaughter_house', $ids)
                ->where([
                    'status' => 'Approved'
                ])->orderBy('id', 'DESC');


            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
            });
        } else if (
            Admin::user()->isRole('administrator') ||
            Admin::user()->isRole('maaif') ||
            Admin::user()->isRole('admin')
        ) {
        } else if (
            Admin::user()->isRole('dvo')
        ) {
            $u = Auth::user();
            $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();
            $dis = Location::find($r->type_id);
            $grid->model()->where([])->orderBy('id', 'DESC');
        } else {
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
            $grid->actions(function ($actions) {
                $status = ((($actions->row['status'])));
                if (
                    $status == 'Pending' ||
                    $status == 'Halted'
                ) {
                } else {
                    $actions->disableDelete();
                    $actions->disableEdit();
                }
            });
        }


        if (Admin::user()->isRole('trader') || Admin::user()->isRole('farmer')) {
        } else {
            $grid->disableCreateButton();
        }


        if (
            Admin::user()->isRole('dvo')
        ) {
            $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();

            if ($r != null) {
                $dis = null;
                if($r != null){
                    $dis = Location::find($r->type_id);
                }
                if ($dis != null) {

                    $grid->header(function ($query) {
                        $r = AdminRoleUser::where(['user_id' => Admin::user()->id, 'role_id' => 7])->first();
                        $dis = Location::find($r->type_id);
                        $move = Movement::where(
                            [
                                'district_from' => $dis->id,
                                'status' => 'pending'
                            ]
                        )->first();
                        if ($move != null) {

                            $content  = '';
                            $content  = 'There is a movement permit application that is prending for verification.';
                            $content .= '<p><a href="' . admin_url('movements/' . $move->id . '/edit') . '" class="btn btn-success  ">Review Application</a></p>';

                            if ($content != "") {
                                $box = new Box('Confirm pending movement - form #' . $move->id, $content);
                                $box->style('danger');
                                $box->solid();
                                return $box;
                            }
                        }
                    });
                }
            }
        } else {
            $grid->model()->orderBy('id', 'DESC');
        }

        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('Date'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();
        $grid->column('destination', __('Destination'))
            ->filter([
                'To farm' => 'To farm',
                'To slaughter' => 'To slaughter',
                'Other' => 'Other',
            ])
            ->sortable();

        $grid->column('destination_slaughter_house', __('Slaughter house'))->display(function ($id) {
            $s = DB::table('slaughter_houses')->where('id', '=', $id)->first();
            if ($s == null) {
                return '-';
            }
            return $s->name;
        })->sortable();
        $grid->column('trader_name', __('Applicant'))->sortable();
        $grid->column('transporter_name', __('Transporter'))->sortable();
        $grid->column('vehicle', __('Vehicle Reg. No.'));

        /*  $grid->column('sub_county_to', __('To subcounty'))->display(function ($user) {
            return $this->subcounty_to_text;
        })->sortable(); */
        $grid->column('animals', __('Animals'))->display(function ($user) {
            $c = DB::table('movement_has_movement_animals')->where('movement_id', '=', $this->id)->count();
            return $c;
        });

        $grid->column('status', __('Permit status'))->label([
            'Pending' => 'warning',
            'Approved' => 'success',
            'Halted' => 'danger',
            'Rejected' => 'danger',
        ], 'success');
        $grid->column('print', __('Print'))
            ->display(function ($f) {
                return '<a target="_blank" href="' . url("print?id=" . $this->id) . '">Print permit</a>';
            });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Movement::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created'));
        $show->field('administrator_id', __('Approved by'))->as(function ($user) {
            $_user = Administrator::find($user);
            if (!$_user) {
                return "-";
            }
            return $_user->name;
        })->sortable();
        $show->field('from', __('From'))->as(function ($id) {
            $item = Farm::find($id);
            if (!$item) {
                return "FARM ID: " . $id;
            }
            return $item->holding_code;
        })->sortable();

        $show->field('to', __('To'))->as(function ($id) {
            $item = Farm::find($id);
            if (!$item) {
                return "FARM ID: " . $id;
            }
            return $item->holding_code;
        })->sortable();
        $show->field('vehicle', __('Vehicle'));
        $show->field('reason', __('Reason'));
        /*
"id" => 2
"created_at" => "2022-01-14 22:02:51"
"updated_at" => "2022-01-14 22:02:51"
"administrator_id" => 196
"sub_county_id" => 3
"parish_id" => 15
"status" => "Sick"
"type" => "Sheep"
"e_id" => "4626314747"
"v_id" => "31073"
"lhc" => "5193"
"breed" => "Ankole"
"sex" => "Male"
"dob" => "2001-11-18"
"color" => "Mixed"
"farm_id" => 93

admin_menu
Expand/CollapseStructureadmin_operation_log
Expand/CollapseStructureadmin_permissions
Expand/CollapseStructureadmin_roles
Expand/CollapseStructureadmin_role_menu
Expand/CollapseStructureadmin_role_permissions
Expand/CollapseStructureadmin_role_users
Expand/CollapseStructureadmin_users
Expand/CollapseStructureadmin_user_permissions
Expand/CollapseStructureanimals
Expand/CollapseStructurearchived_animals
Expand/CollapseStructurediseases 
Expand/CollapseStructureevents
Expand/CollapseStructurefailed_jobs
Expand/CollapseStructurefarms
Expand/CollapseStructuremedicines
Expand/CollapseStructuremigrations
Expand/CollapseStructuremovements
Expand/CollapseStructuremovement_animals
Expand/CollapseStructuremovement_has_movement_animals
Expand/CollapseStructurepassword_resets
Expand/CollapseStructurepersonal_access_tokens
Expand/CollapseStructureslaughter_records
Expand/CollapseStructuresub_counties
Expand/CollapseStructureusers
Expand/CollapseStructurevaccines

*/




        $show->field('animals', __('Animals'))->unescape()->as(function ($a) {
            $headers = ['Id', 'E-ID', 'V-ID', 'Breed', 'SEX', 'Color', 'Details'];
            $rows = [];
            foreach ($this->movement_has_movement_animals as $key => $an) {
                $row = [];
                $row[] = $an->animal->id;
                $row[] = $an->animal->e_id;
                $row[] = $an->animal->v_id;
                $row[] = $an->animal->breed;
                $row[] = $an->animal->sex;
                $row[] = $an->animal->color;
                $row[] = '<a href="' . admin_url("animals/" . $an->animal->id) . '">Read More</a>';
                //dd($an->animal);
                $rows[] = $row;
            }
            $table = new Table($headers, $rows);
            return $table;
        });


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


        $form = new Form(new Movement());
        $u =   Admin::user();

        if (
            Admin::user()->isRole('dvo') ||
            Admin::user()->isRole('svo')
        ) {

            if (!$form->isEditing()) {
                return $form;
            }
            $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri_segments = explode('/', $uri_path);
            $id = $uri_segments[3];
            $m = Movement::find($id);

            if ($m == null) {
                $id = $uri_segments[2];
                $m = Movement::find($id);
            }

            if ($m == null) {
                $id = $uri_segments[1];
                $m = Movement::find($id);
            }

            if ($m == null) {
                $id = $uri_segments[4];
                $m = Movement::find($id);
            }

            if ($m == null) {
                admin_error('Form not found.');
                return $form;
            }

            $form->divider('Applicant\'s information');
            $form->setTitle("Reviewing Livestock Movement Permit [LMP]");
            $form->hidden('administrator_id')->default(Admin::user()->id);
            $form->display('trader_name', __('Applicant \'s name'))->default($u->name);
            $form->display('trader_nin', __('Applicant\'s NIN'))->default($u->nin);
            $form->display('trader_phone', __('Applicant\'s Phone number'))->default($u->phone_number);


            $form->divider('Animals\' departure info. <b>(FROM)</b>');
            $form->display('subcounty_from_text', __('From Sub-county'))->default('$u->nin');
            $form->display('village_from', __('from Village'));


            $form->divider('Animals\' Destination Info. <b>(TO)</b>');

            $form->display('destination', __('Destination of movement'));
            $form->display('subcounty_to_text', __('Destination Sub-County'));

            $form->divider('Transpotation info.');
            $form->display('transportation_route', __('Transportation route'));
            $form->display('vehicle', __('Vehicle\'s reg\' no.'));
            $form->display('transporter_name', __('Transporter\'s name'));
            $form->display('transporter_nin', __('Transporter\'s NIN'));
            $form->display('transporter_Phone', __('Transporter\'s Phone number'));

            $form->divider('Animals\' to be moved');

            $_d = "<ol>";
            $_i = 0;
            foreach ($m->animals as $an) {
                $_i++;
                $_d .= "<li>  <b>V-ID:</b> $an->v_id, <b>E-ID:</b> $an->e_id, <b>SPECIES:</b> $an->type, - SEX: $an->sex  - <a 
                target=\"_blank\"
                href=\"" .
                    admin_url('animals/' . $an->id)
                    . "\">veiw details</a></li>";
            }
            $_d .= "</ol>";
            $form->html($_d);

            $form->divider('Review movement permit');

            $form->radio('status', __('Review permit'))
                ->options(array(
                    'Approved' => 'Approve',
                    'Halted' => 'Halt',
                    'Rejected' => 'Reject',

                ))
                ->required()
                ->when('Halted', function (Form $form) {
                    $form->textarea('details', __('Reason for halt'));
                })
                ->when('Rejected', function (Form $form) {
                    $form->textarea('details', __('Reason for rejection'));
                })
                ->when('Approved', function (Form $form) {

                    $form->date('valid_from_Date', __('Valid from Date'))->rules('required');
                    $form->date('valid_to_Date', __('Valid until'))->rules('required');
                });
        } else if (
            Admin::user()->isRole('trader') ||
            Admin::user()->isRole('farmer')
        ) {


            $form->setTitle("Applying for Livestock Movement Permit [LMP]");

            $form->divider('Applicant\'s information');
            $form->hidden('<h4 style="padding: 0px!important; margin: 0px!important;">Teader\'s info.</h4>')->readonly();
            $form->hidden('administrator_id')->default(Admin::user()->id);
            $form->text('trader_name', __('Applicant \'s name'))->default($u->name)->rules('required');
            $form->text('trader_nin', __('Applicant\'s NIN'))->default($u->nin)->rules('required');
            $form->text('trader_phone', __('Applicant\'s Phone number'))->rules('required')->default($u->phone_number);



            $form->divider('Animals\' departure info. <b>(FROM)</b>');
            $items = Location::get_sub_counties_array();
            $form->select('sub_county_from', __('Subcounty from'))
                ->options($items)
                ->required();
            $form->text('village_from', __('Village from'))->required();


            $form->divider('Animals\' Destination Info. <b>(TO)</b>');
            $form->radio('destination', __('Destination of movement'))
                ->options(array(
                    'To farm' => 'To another farm',
                    'To slaughter' => 'To slaughter house',
                    'Other' => 'Other',

                ))
                ->required()
                ->when('To farm', function (Form $form) {
                    $farms = [];
                    foreach (Farm::all() as $key => $f) {
                        $farms[$f->id] = $f->holding_code . " - " . $f->owner()->username . " - " . $f->owner()->name;
                    }
                    $form->select('destination_farm', __('Select Farm'))
                        ->rules('required')
                        ->options($farms);
                })
                ->when('To slaughter', function (Form $form) {
                    $houses = SlaughterHouse::all();
                    $_farms = [];
                    foreach ($houses as $key => $f) {
                        $_farms[$f->id] = $f->id . " - " . $f->name . " - " . $f->subcounty->name_text;
                    }

                    $form->select('destination_slaughter_house', __('Select slaughter house'))
                        ->options($_farms)
                        ->rules('required')
                        ->help('Please select slaughter house');
                })
                ->when('Other', function (Form $form) {
                    $items = Location::get_sub_counties_array();
                    $form->select('sub_county_to', __('Subcounty to'))
                        ->options($items)->rules('required');
                    $form->text('village_to', __('Village to'))
                        ->rules('required');
                    $form->text('reason', __('Specify purpose of movement'))->rules('required');
                });
            $form->text('transportation_route', __('Transportation route'))->required();


            $form->divider();
            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Transpotation info.</h4>');
            $form->text('vehicle', __('Vehicle\'s reg\' no. '))->required();
            $form->text('transporter_name', __('Transporter name'))->required();
            $form->text('transporter_nin', __('Transporter\'s NIN'))->required();
            $form->text('transporter_Phone', __('transporter\'s Phone'));
            $form->divider('Animals to be moved');


            /*       
    $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Payment info.</h4>');
    $form->radio('paid_method', __('Payment method'))
                ->options(array(
                    'Mobile money' => 'Mobile money',
                    'Bank' => 'Bank',
                ));
 */


            $_items = [];
            foreach (Animal::where('administrator_id', '=', Admin::user()->id)
                ->where('status', '!=', 'sold')
                ->where('trader', '<', 1)
                ->get()  as $key => $item) {
                $_items[$item->id] = $item->e_id . " - " . $item->v_id;
            }

            $form->listbox('animals', 'Animals to be moved')->options($_items)
                ->rules('required');


            /*             $form->text('details', __('NOTE')); */


            $form->disableEditingCheck();
            $form->disableViewCheck();
            $form->disableCreatingCheck();
        }


        if (
            Admin::user()->isRole('administrator')
            ||
            Admin::user()->isRole('admin') ||
            Admin::user()->isRole('dvo')
        ) {
            /* $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Payment info.</h4>');

            $form->hidden('is_paid', __('Payment status'))
                ->value('Paid')
                ->default('Paid');

            $form->radio('paid_method', __('Payment method'))
                ->options(array(
                    'Mobile money' => 'Mobile money',
                    'Bank' => 'Bank',
                ))
                ->required();
                 $form->divider();
            */
        }



        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();
        $form->disableReset();
        return $form;
    }
}
