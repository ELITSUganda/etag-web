<?php

namespace App\Admin\Controllers;

use App\Models\NotificationModel;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class NotificationModelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'NotificationModel';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new NotificationModel());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('title', __('Title'));
        $grid->column('message', __('Message'));
        $grid->column('data', __('Data'));
        $grid->column('reciever_id', __('Reciever id'));
        $grid->column('status', __('Status'));
        $grid->column('type', __('Type'));
        $grid->column('image', __('Image'));

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
        $show = new Show(NotificationModel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('title', __('Title'));
        $show->field('message', __('Message'));
        $show->field('data', __('Data'));
        $show->field('reciever_id', __('Reciever id'));
        $show->field('status', __('Status'));
        $show->field('type', __('Type'));
        $show->field('image', __('Image'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $reciever_id = 709;
        $msg = "Simple test message";
        $title = "NOT RECEIVED - {$reciever_id}";
        Utils:: sendNotification(
            $msg,
            $reciever_id,
            $headings =  $title,
            $data = [
                'type' => 'Animal',
                'id' => 1,
            ]
        );
        $form = new Form(new NotificationModel());

        $form->text('title', __('Title'));
        $form->textarea('message', __('Message'));
        $form->textarea('data', __('Data'));
        $form->text('reciever_id', __('Reciever id'));
        $form->text('status', __('Status'))->default('NOT READ');
        $form->text('type', __('Type'));
        $form->textarea('image', __('Image'));

        return $form;
    }
}
