<?php

namespace Encore\Admin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.administrator');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel());

        $grid->filter(function ($filter) {
            $filter->like('name', "Name");
            $filter->like('phone_number', "Phone number");
        });
        
        $grid->column('id', 'ID')->sortable();
        $grid->column('created_at', __('Created'))
        ->display(function ($f) {
            return Carbon::parse($f)->toFormattedDateString();
        })->sortable();

        $grid->column('name', trans('admin.name'))->sortable();
        $grid->column('username', trans('admin.username'));
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();
        $grid->column('gender', trans('Gender'))->sortable();
        $grid->column('phone_number', trans('Phone number'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $userModel = config('admin.database.users_model');

        $show = new Show($userModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('gender', trans('Gender'));
        $show->field('nin', trans('National ID number'));
        $show->field('phone_number', trans('Phone number 1'));
        $show->field('phone_number_2', trans('Phone number 2'));
        $show->field('email', trans('Email address'));
        $show->field('details', trans('Details'));


        $show->field('username', trans('admin.username'));
        $show->field('roles', trans('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();
        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('created_at', trans('Created'))->as(function ($f) {
            return Carbon::parse($f)->toFormattedDateString();
        });
        $show->field('updated_at', trans('Last updated'))->as(function ($f) {
            return Carbon::parse($f)->toFormattedDateString();
        }); 

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->display('id', 'ID');
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', trans('admin.name'))->rules('required');

        $form->select('gender', __('Gender'))
        ->options(array(
            'Male' => 'Male',
            'Female' => 'Female'
        ))
        ->required();

        $form->text('phone_number', "Phone number 1")->required();
        $form->text('phone_number_2', "Phone number 2");
        $form->text('email', "Email Address");
        $form->text('nin', "National ID No.");
        $form->text('address', trans('Address'));
        $form->textarea('details', trans('Details'));

        $form->divider();

        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
        $form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));
 
        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });



        return $form;
    }
}
