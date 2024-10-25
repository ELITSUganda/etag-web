<?php

namespace App\Admin\Controllers;

use App\Models\ApplicationType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ApplicationTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Application Types';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ApplicationType());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('name', __('Name'));
        $grid->column('description', __('Description'));
        $grid->column('fields', __('Fields'));
        $grid->column('message_1', __('Message 1'));
        $grid->column('message_2', __('Message 2'));
        $grid->column('message_3', __('Message 3'));
        $grid->column('is_paid', __('Is paid'));
        $grid->column('documents', __('Documents'));

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
        $show = new Show(ApplicationType::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('fields', __('Fields'));
        $show->field('message_1', __('Message 1'));
        $show->field('message_2', __('Message 2'));
        $show->field('message_3', __('Message 3'));
        $show->field('is_paid', __('Is paid'));
        $show->field('documents', __('Documents'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ApplicationType());

        $form->text('name', __('Application Name'))->rules('required');
        $form->text('message_2', __('Application Title'))->rules('required');
        $form->text('description', __('Short Description'))->rules('required')->required();
        $form->ckeditor('message_1', __('Template'))
            ->options([
                'height' => 600,
                'toolbar' => [
                    ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'],
                    ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'],
                    ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
                    ['Link', 'Unlink', 'Anchor'],
                    ['Image', 'Table', 'HorizontalRule', 'SpecialChar'],
                    '/',
                    ['Styles', 'Format', 'Font', 'FontSize'],
                    ['TextColor', 'BGColor'],
                    ['Maximize', 'ShowBlocks', '-', 'Source'],
                ],
                'placeholder' => 'Compose an epic...',
            ]);

        $form->html(
            '<code>[NAME_OF_APPLICANT]</code>' .
                '<code>[APPLICANT_ADDRESS]</code>' .
                '<code>[COUNTRY_OF_ORIGIN]</code>' .
                '<code>[SINGLE_ANIMAL_TABLE]</code>' .
                '',
            "Keywords"
        );

        return $form;
        $form->quill('message_3', __('Message 3'));
        $form->text('is_paid', __('Is paid'))->default('Yes');
        $form->tags('documents', __('Documents'));
        $form->textarea('fields', __('Fields'));

        return $form;
    }
}
