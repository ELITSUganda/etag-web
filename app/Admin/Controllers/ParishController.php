<?php

namespace App\Admin\Controllers;

use App\Models\Parish;
use App\Models\SubCounty;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ParishController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Parish';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Parish());
 


        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('sub_county_id', __('Sub county'))->display(function ($sub_county_id) {
            $d = SubCounty::find($sub_county_id);
            return $d->name;
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
        $show = new Show(Parish::findOrFail($id)); 

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('detail', __('Detail'));
        

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Parish());



        
        $form->setWidth(8, 4);
        $items = [];
        foreach (SubCounty::all() as $key => $v) { 
            $items[$v->id] = $v->name.", ".$v->district->name.".";
        }
        $form->text('name', __('Sub county Name'))->required();
        $form->select('sub_county_id', __('Sub county'))
            ->options($items)
            ->required();

        $form->textarea('detail', __('Detail'));

        return $form;
    }


}
