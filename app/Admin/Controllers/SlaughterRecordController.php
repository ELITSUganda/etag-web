<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterRecord;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SlaughterRecordController extends AdminController
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
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('e_id', 'EID');
            $filter->like('lhc', 'LHC');
            //created_at range
            $filter->between('created_at', 'Date');
            $filter->between('dob', 'DoB');
        });


        $grid->model()->orderBy('id', 'DESC');
        $grid->column('created_at', __('Date'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();
        $grid->column('lhc', __('LHC'))->sortable()->hide();
        $grid->column('v_id', __('V ID'))->hide();
        $grid->column('e_id', __('E-ID'))->sortable();
        $grid->column('breed', __('Satus'))
            ->display(function ($f) {
                $isDone = false;
                if (strtolower($f) == 'done') {
                    $isDone = true;
                }
                return $isDone ? "<span class='label label-success'>Completed</span>" : "<span class='label label-danger'>Ongoing</span>";
            })->sortable();
        $grid->column('sex', __('Sex'))->sortable();
        $grid->column('dob', __('Dob'))->sortable();
        $grid->column('fmd', __('Last FMD'))->sortable()
            ->display(function ($f) {
                if ($f == null || strlen($f) < 4) {
                    return "N/A";
                }
                return Carbon::parse($f)->toFormattedDateString();
            });
        $grid->column('destination_slaughter_house', __('Destination slaughter house'))
            ->display(function ($f) {
                return $f;
            })->sortable()
            ->hide();
        $grid->column('details', __('Details'))->hide();
        $grid->column('administrator_id', __('Slaughtered By'))
            ->display(function ($f) {
                $u = Administrator::find($f);
                if ($u == null) {
                    return "N/A";
                }
                return $u->name;
            })->sortable();
        /*         $grid->column('bar_code', __('Bar Code'))
            ->lightbox(['width' => 50, 'height' => 50]); */
        $grid->column('post_grade', __('Meat Grade'))
            ->display(function ($f) {
                return $f;
            })->sortable();
        $grid->column('post_age', __('Animal\'s Age'))
            ->display(function ($f) {
                $dob = Carbon::parse($this->dob);
                $now = Carbon::parse($this->created_at);
                return $dob->diffInMonths($now) . " months";
            })->sortable();
        $grid->column('post_dentition', __('Dentition'))
            ->display(function ($f) {
                return $f;
            })->sortable();
        $grid->column('post_weight', __('Weight'))
            ->display(function ($f) {
                return $f . " Kgs";
            })->sortable();
        $grid->column('post_fat', __('Post fat'))
            ->display(function ($f) {
                return $f . " mm";
            })->sortable();
        $grid->column('post_other', __('Premortem Findings'))
            ->display(function ($f) {
                //from json
                $data = json_decode($f);
                if ($data == null) {
                    return "N/A";
                }
                $dp = "";
                foreach ($data as $key => $value) {
                    //separator ,
                    $dp .=  $value . ", "; 
                }
                return $dp;
            })->sortable()
            ->filter('like');


        $grid->disableCreateButton();
        $grid->disableBatchActions();
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
        $show->field('administrator_id', __('Administrator id'));
        $show->field('type', __('Type'));
        $show->field('bar_code', __('Bar code'));
        $show->field('post_grade', __('Post grade'));
        $show->field('post_animal', __('Post animal'));
        $show->field('post_age', __('Post age'));
        $show->field('post_dentition', __('Post dentition'));
        $show->field('post_weight', __('Post weight'));
        $show->field('post_fat', __('Post fat'));
        $show->field('post_other', __('Post other'));
        $show->field('has_post_info', __('Has post info'));
        $show->field('available_weight', __('Available weight'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SlaughterRecord());

        $form->textarea('lhc', __('Lhc'));
        $form->textarea('v_id', __('V id'));
        $form->textarea('e_id', __('E id'));
        $form->textarea('breed', __('Breed'));
        $form->textarea('sex', __('Sex'));
        $form->textarea('dob', __('Dob'));
        $form->textarea('fmd', __('Fmd'));
        $form->textarea('destination_slaughter_house', __('Destination slaughter house'));
        $form->textarea('details', __('Details'));
        $form->number('administrator_id', __('Administrator id'))->default(14);
        $form->textarea('type', __('Type'));
        $form->textarea('bar_code', __('Bar code'));
        $form->textarea('post_grade', __('Post grade'));
        $form->textarea('post_animal', __('Post animal'));
        $form->textarea('post_age', __('Post age'));
        $form->textarea('post_dentition', __('Post dentition'));
        $form->textarea('post_weight', __('Post weight'));
        $form->textarea('post_fat', __('Post fat'));
        $form->textarea('post_other', __('Post other'));
        $form->textarea('has_post_info', __('Has post info'));
        $form->textarea('available_weight', __('Available weight'));

        return $form;
    }
}
