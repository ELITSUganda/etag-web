<?php

namespace App\Admin\Controllers;

use App\Models\AdminRole;
use App\Models\AdminRoleUser;
use App\Models\CheckPoint;
use App\Models\Location;
use App\Models\SlaughterHouse;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AdminRoleUserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Admin Roles';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    { /*
        $i = 0;
        foreach (AdminRoleUser::all() as $r) {
            $i++;
            $r->id = $i;
            $r->save();   
        }  
         
        $d = AdminRoleUser::find(33);
        dd($d);
        $i = 0;
        foreach (AdminRoleUser::all() as $r) {
            $i++;
            $r->id = $i;
            $r->save();   
        } */
        $grid = new Grid(new AdminRoleUser());
        $grid->disableBatchActions();


        $grid->model()->orderBy('id', 'DESC');

        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();
            $ajax_url = url(
                '/api/ajax-users'
            );
            $filter->equal('user_id', 'Filter by User')
                ->select(function ($id) {
                    $a = Administrator::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);

            $filter->equal('type_id', 'Filter by district')
                ->select(function ($id) {
                    $a = Location::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax(url('/api/ajax-users'));

            $filter->equal('type_id', 'Filter by sub-county')
                ->select(function ($id) {
                    $a = Location::find($id);
                    if ($a) {
                        return [$a->id => $a->name_text];
                    }
                })->ajax(url('/api/sub-counties'));

            $roles = [];
            foreach (AdminRole::all() as $key => $v) {
                $roles[$v->id] = $v->name;
            }
            $filter->equal('role_id', 'Filter by Role')
                ->select($roles);
        });

        $grid->model()->orderBy('id', 'desc');




        $grid->column('id', __('ID'))->sortable();

        $grid->column('user_id', __('User'))
            ->display(function () {
                if ($this->owner == null) {
                    $this->delete();
                    return "-";
                }
                return $this->owner->name;
            })
            ->sortable();
        $grid->column('role_id', __('Role'))
            ->display(function () {
                return $this->role->name;
            })
            ->sortable();
        $grid->column('type_id', __('Assigned to'))
            ->display(function () {
                if ($this->type == null) {
                    return "-";
                }
                return $this->type->name;
            })
            ->sortable();

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
        $show = new Show(AdminRoleUser::findOrFail($id));

        $show->field('role_id', __('Role id'));
        $show->field('user_id', __('User id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('id', __('Id'));
        $show->field('role_type', __('Role type'));
        $show->field('type_id', __('Type id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AdminRoleUser());

        $form->select('user_id', 'Select user')
            ->options(function ($id) {
                $parent = Administrator::find($id);
                if ($parent != null) {
                    return [$parent->id =>  $parent->name];
                }
            })
            ->rules('required')
            ->ajax(
                url('/api/ajax-users')
            );

        $form->radio('role_type', 'Role category')
            ->options([
                'dvo' => 'D.V.O',
                'scvo' => 'S.C.V.O',
                'check-point-officer' => 'Checkpoint officer',
                'slaughter' => 'Slaugter house officer',
                'other' => 'Other',
            ])
            ->when('dvo', function ($f) {
                $f->hidden('role_id', __('Role id'))->default(7);
                $f->select('type_id_1', 'Select District')
                    ->options(function ($id) {
                        $parent = Location::find($id);
                        if ($parent != null) {
                            return [$parent->id =>  $parent->name];
                        }
                    })
                    ->rules('required')
                    ->ajax(
                        url('/api/districts')
                    );
            })
            ->when('scvo', function ($f) {
                $f->hidden('role_id', __('Role id'))->default(2);
                $f->select('type_id', 'Select sub-county')
                    ->options(function ($id) {
                        $parent = Location::find($id);
                        if ($parent != null) {
                            return [$parent->id =>  $parent->name];
                        }
                    })
                    ->rules('required')
                    ->ajax(
                        url('/api/sub-counties')
                    );
            })
            ->when('check-point-officer', function ($f) {
                $f->hidden('role_id', __('Role id'))->default(9);
                $f->select('type_id_3', 'Select checkpoint')
                    ->options(function ($id) {
                        return CheckPoint::all()->pluck('name', 'id');
                    })
                    ->rules('required');
            })
            ->when('slaughter', function ($f) {
                $f->hidden('role_id', __('Role id'))->default(5);
                $f->select('type_id_4', 'Select slaughter house')
                    ->options(function ($id) {
                        return SlaughterHouse::all()->pluck('name_text', 'id');
                    })
                    ->rules('required');
            })
            ->when('other', function ($f) {
                $roles = [];
                foreach (AdminRole::all() as $key => $v) {
                    if ($v->id == 7 || $v->id == 2 || $v->id == 9 || $v->id == 5) {
                        continue;
                    }
                    $roles[$v->id] = $v->name . " " . $v->slug . " " . $v->id;
                }
                $f->radio('role_id', 'Select Role')
                    ->options($roles)
                    ->rules('required');
            })
            ->rules('required');


        return $form;
    }
}
