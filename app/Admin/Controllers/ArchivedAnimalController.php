<?php

namespace App\Admin\Controllers;

use App\Models\ArchivedAnimal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ArchivedAnimalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Archived Animals';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ArchivedAnimal());

        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableBatchActions(); 

        $grid->column('id', __('Id'));
        $grid->column('e_id', __('E id'));
        $grid->column('v_id', __('V id'));
        $grid->column('lhc', __('Lhc'));
        $grid->column('sex', __('Sex'));
        $grid->column('breed', __('Breed'));
        $grid->column('dob', __('Dob'));
        $grid->column('owner', __('Owner'));
        $grid->column('last_event', __('Last event'));
        $grid->column('type', __('Speicies'));
        
        $grid->column('district', __('District'));
        $grid->column('sub_county', __('Sub county'));
        $grid->column('events', __('Events'))->hide();
        $grid->column('details', __('Details'))->hide();
        $grid->column('created_at', __('Created at'))->hide();

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
        $show = new Show(ArchivedAnimal::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('owner', __('Owner'));
        $show->field('district', __('District'));
        $show->field('sub_county', __('Sub county'));
        $show->field('type', __('Type'));
        $show->field('e_id', __('E id'));
        $show->field('v_id', __('V id'));
        $show->field('lhc', __('Lhc'));
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        $show->field('dob', __('Dob'));
        $show->field('last_event', __('Last event'));
        $show->field('events', __('Events'));
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
        $form = new Form(new ArchivedAnimal());

        $form->textarea('owner', __('Owner'));
        $form->textarea('district', __('District'));
        $form->textarea('sub_county', __('Sub county'));
        $form->textarea('type', __('Type'));
        $form->textarea('e_id', __('E id'));
        $form->textarea('v_id', __('V id'));
        $form->textarea('lhc', __('Lhc'));
        $form->textarea('breed', __('Breed'));
        $form->textarea('sex', __('Sex'));
        $form->textarea('dob', __('Dob'));
        $form->textarea('last_event', __('Last event'));
        $form->textarea('events', __('Events'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
