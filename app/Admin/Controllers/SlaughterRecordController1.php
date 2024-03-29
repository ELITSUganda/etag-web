<?php

namespace App\Admin\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\ArchivedAnimal;
use App\Models\Movement;
use App\Models\SlaughterRecord;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Hamcrest\Util;
use Illuminate\Support\Facades\Auth;
use Monolog\Handler\Slack\SlackRecord;

class SlaughterRecordController1 extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Slaughter Records';

    /** 
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SlaughterRecord());

        $grid->model()->orderBy('id', 'DESC');

        $grid->disableActions();
        $grid->disableBatchActions();
        if (!Admin::user()->isRole('slaughter')) {
            $grid->disableCreateButton();
        }

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();
        $grid->column('e_id', __('EID'))->sortable();
        $grid->column('v_id', __('VID'))->sortable();
        $grid->column('lhc', __('LHC'))->sortable();
        $grid->column('type', __('Species'))->sortable();
        $grid->column('breed', __('Breed'))->sortable();
        $grid->column('sex', __('Sex'))->sortable();
        $grid->column('dob', __('DoB'))->sortable();
        $grid->column('fmd', __('FMD'))->sortable();
        $grid->column('destination_slaughter_house', __('Abattoir'))->sortable();
        $grid->column('details', __('Details'))->hide();


        return $grid;
    }





    /**
     * 
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(SlaughterRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('lhc', __('Lhc'));
        $show->field('v_id', __('V id'));
        $show->field('e_id', __('E id'));
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        $show->field('dob', __('Dob'));
        $show->field('fmd', __('Fmd'));
        $show->field('destination_slaughter_house', __('Destination slaughter house'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        /*$form = new WidgetForm();
        $form->email('email')->default('qwe@aweq.com');
        $form->password('password');
        $form->text('name'); */



        $form = new Form(new Animal());

        $form->submitted(function (Form $form) {
            if (
                isset($_POST['animals']) &&
                isset($_POST['accept'])

            ) {
                foreach ($_POST['animals'] as $key => $value) {


                    $an = Animal::find($value);
                    if ($an == null) {
                        continue;
                    }

                    $u  = Admin::user();
                    $sr = new SlaughterRecord();
                    $sr->lhc = $an->lhc;
                    $sr->v_id = $an->v_id;
                    $sr->administrator_id = Admin::user()->id;
                    $sr->e_id = $an->e_id;
                    $sr->type = $an->type;
                    $sr->breed = $an->breed;
                    $sr->sex = $an->sex;
                    $sr->dob = $an->dob;
                    $sr->fmd = $an->fmd;
                    $sr->details = $_POST['details'];
                    $sr->destination_slaughter_house = $u->name;

                    $sr->save();


                    $details = "Slautered by " . $u->name . ", ID " . $u->id;
                    Utils::archive_animal([
                        'animal_id' => $value,
                        'details' => $details,
                        'event' => 'Slautered',
                    ]);
                }
            }


            return redirect(admin_url("slaughter-records"));
        });


        $permists = Movement::where('destination_slaughter_house', '=', Admin::user()->id)
            ->where('status', '=', 'Approved')->get();
        $_items = [];


        if (Admin::user()->isRole('slaughter')) {
            $u = Auth::user();
            $recs = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 5])->get();
            $ids = [];
            foreach ($recs as $rec) {
                $ids[] = $rec->type_id;
            }

            foreach (Animal::where('slaughter_house_id', $ids)->get() as $key => $item) {
                $_items[$item->id] = $item->e_id . " - " . $item->v_id;
            }
        }


        $form->multipleSelect('animals', __('Select animals to slaughter'))
            ->options($_items)
            ->required();

        $form->textarea('details', 'Details');
        $form->checkbox('accept', "Are you sure you have slaughtered animals selected?")->options([1 => 'Yes'])->required();



        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableReset();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });
        return $form;
    }
}
