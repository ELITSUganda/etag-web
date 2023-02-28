<?php

namespace App\Admin\Controllers;

use App\Models\SubCounty;
use App\Models\District;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SubCountyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SubCounty';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SubCounty());

        /*$d[] = 'Adjumani Tc';
        $d[] = 'Adropi';
        $d[] = 'Ciforo';
        $d[] = 'Dzaipi';
        $d[] = 'Ofua';
        $d[] = 'Pakelle';
        foreach ($d as $key => $value) {
            $sub = new SubCounty();
            $sub->name = $value;
            $sub->district_id = 2;
            $sub->administrator_id = 2;
            $sub->save();
        }
        dd($d);*/


        $grid->filter(function ($filter) {
            $districts = [];
            foreach (District::all() as $key => $p) {
                $districts[$p->id] = $p->name . " - " . $p->code;
            }
            $filter->equal('district_id', "District")->select($districts);
        });

        $grid->column('id', __('Id'))->sortable()->width(40);
        $grid->column('name', __('Name'))->sortable()->width(100);
        $grid->column('code', __('CODE'))->sortable()->width(80);
        $grid->column('locked_down', __('Quarantine'))
            ->display(function ($l) {
                if ($l) {
                    return "Locked down";
                } else {
                    return "Open";
                }
            })
            ->sortable()->width(120);
        $grid->column('district_id', __('District'))->display(function ($district_id) {
            $d = District::find($district_id);
            if (!$d) {
                return "-";
            }
            return $d->name;
        })->sortable()->width(100);

        $grid->column('administrator_id', __('S.V.O'))->display(function ($user) {
            $_user = Administrator::find($user);
            if (!$_user) {
                return "-";
            }
            return $_user->name;
        })->sortable();

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
        $show = new Show(SubCounty::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('detail', __('Detail'));
        $show->field('administrator_id', __('Administrator id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new SubCounty());
        $form->setWidth(8, 4);
        $admins = [];
        foreach (Administrator::all() as $key => $v) {
            if (!$v->isRole('scvo')) {
                continue;
            }
            $admins[$v->id] = $v->name . " - " . $v->id;
        }


        $form->text('name', __('Subcounty Name'))->required();
        $form->text('code', __('CODE'))->readonly();
        $form->select('district_id', __('District'))
            ->options(District::all()->pluck("name", 'id'))
            ->required(); 

        $form->select('administrator_id', __('SubCounty veterinary officer'))
            ->options($admins)
            ->required();
        $form->textarea('detail', __('Detail'));

        $form->radio('locked_down', 'Quarantine')->options([
            0 => 'Opened',
            1 => 'Lock down',
        ])
            ->default(0)
            ->help('NOTE: Lock down means no movement of livestock will be allowed in that region  until opened.');

        return $form;
    }
}
