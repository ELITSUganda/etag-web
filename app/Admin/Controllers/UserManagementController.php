<?php

namespace App\Admin\Controllers;

use App\Models\Location;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User Management';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Administrator());

        // Customize grid appearance
        $grid->model()->orderBy('id', 'desc');
        
        // Enable batch actions
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });

        // Add filter and export
        $grid->filter(function($filter){
            // Remove default ID filter
            $filter->disableIdFilter();
            
            // Add search filters
            $filter->like('name', 'Name');
            $filter->like('phone_number', 'Phone Number');
            $filter->like('email', 'Email');
            $filter->like('username', 'Username');
            
            // Status filter
            $filter->equal('status', 'Status')->select([
                'Active' => 'Active',
                'Inactive' => 'Inactive'
            ]);
            
            // Gender filter
            $filter->equal('gender', 'Gender')->select([
                'Male' => 'Male',
                'Female' => 'Female'
            ]);
            
            // Role filter
            $filter->where(function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->where('role_id', $this->input);
                });
            }, 'Role')->select(Role::all()->pluck('name', 'id'));
            
            // Location filters
            $filter->equal('district_id', 'District')->select(
                Location::where('parent', 0)->pluck('name', 'id')
            );
            
            $filter->equal('sub_county_id', 'Sub County')->select(
                Location::where('parent', '!=', 0)->pluck('name', 'id')
            );
            
            // User type filter
            $filter->equal('user_type', 'User Type')->select([
                'Admin' => 'Admin',
                'Worker' => 'Worker',
                'Farmer' => 'Farmer'
            ]);
            
            // Date filters
            $filter->between('created_at', 'Registration Date')->datetime();
        });

        // Column definitions
        $grid->column('id', __('ID'))->sortable();
        
        $grid->column('avatar', __('Avatar'))->image('', 50, 50);
        
        $grid->column('name', __('Full Name'))->display(function () {
            return $this->name ?: ($this->first_name . ' ' . $this->last_name);
        })->sortable();
        
        $grid->column('username', __('Username'))->sortable();
        
        $grid->column('phone_number', __('Phone'))->display(function ($phone) {
            return $phone ?: '-';
        });
        
        $grid->column('email', __('Email'))->display(function ($email) {
            return $email ?: '-';
        })->sortable();
        
        $grid->column('roles', __('Roles'))->display(function ($roles) {
            $roles = array_map(function ($role) {
                return "<span class='label label-success'>{$role['name']}</span>";
            }, $roles);
            return join('&nbsp;', $roles);
        });
        
        $grid->column('farms_count', __('Farms'))->display(function () {
            $count = \DB::table('farms')->where('administrator_id', $this->id)->count();
            return "<span class='badge badge-info'>{$count}</span>";
        })->sortable(false);
        
        $grid->column('animals_count', __('Animals'))->display(function () {
            $count = \DB::table('animals')->where('administrator_id', $this->id)->count();
            return "<span class='badge badge-primary'>{$count}</span>";
        })->sortable(false);
        
        $grid->column('events_count', __('Events'))->display(function () {
            $count = \DB::table('events')->where('administrator_id', $this->id)->count();
            return "<span class='badge badge-warning'>{$count}</span>";
        })->sortable(false);
        
        $grid->column('status', __('Status'))->display(function ($status) {
            return $status == 1 
                ? "<span class='label label-success'>Active</span>" 
                : "<span class='label label-danger'>Inactive</span>";
        })->sortable();
        
        $grid->column('user_type', __('Type'))->display(function ($type) {
            return $type ?: 'Farmer';
        });
        
        $grid->column('created_at', __('Registered'))->display(function ($date) {
            return date('Y-m-d', strtotime($date));
        })->sortable();

        // Quick actions
        $grid->actions(function ($actions) {
            // Add custom actions
            $actions->append('<a href="' . admin_url('user-management/' . $actions->getKey() . '/edit') . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a>');
        });

        // Batch actions
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        // Export
        $grid->exporter(function ($export) {
            $export->filename('Users_Export');
            $export->except(['avatar', 'password', 'remember_token']);
        });

        // Quick search
        $grid->quickSearch('name', 'phone_number', 'email', 'username');

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
        $show = new Show(Administrator::findOrFail($id));

        $show->panel()->tools(function ($tools) {
            $tools->disableDelete();
        });

        // Basic Information
        $show->divider('Basic Information');
        $show->field('id', __('ID'));
        $show->field('avatar', __('Avatar'))->image();
        $show->field('name', __('Full Name'));
        $show->field('first_name', __('First Name'));
        $show->field('last_name', __('Last Name'));
        $show->field('username', __('Username'));
        $show->field('gender', __('Gender'));
        $show->field('nin', __('National ID'));
        
        // Contact Information
        $show->divider('Contact Information');
        $show->field('phone_number', __('Phone Number'));
        $show->field('phone_number_2', __('Alternative Phone'));
        $show->field('email', __('Email'));
        $show->field('address', __('Address'));
        
        // Location Information
        $show->divider('Location');
        $show->field('district', __('District'))->as(function () {
            if ($this->district_id) {
                $district = Location::find($this->district_id);
                return $district ? $district->name : '-';
            }
            return '-';
        });
        $show->field('sub_county', __('Sub County'))->as(function () {
            if ($this->sub_county_id) {
                $subCounty = Location::find($this->sub_county_id);
                return $subCounty ? $subCounty->name : '-';
            }
            return '-';
        });
        
        // Account Information
        $show->divider('Account Information');
        $show->field('status', __('Status'))->using([
            'Active' => 'Active',
            'Inactive' => 'Inactive'
        ])->label([
            'Active' => 'success',
            'Inactive' => 'danger'
        ]);
        $show->field('user_type', __('User Type'));
        $show->field('language', __('Language'));
        
        // Roles and Permissions
        $show->divider('Roles and Permissions');
        $show->field('roles', __('Assigned Roles'))->as(function ($roles) {
            return collect($roles)->pluck('name')->implode(', ');
        })->label('success');
        
        $show->field('permissions', __('Direct Permissions'))->as(function ($permissions) {
            return collect($permissions)->pluck('name')->implode(', ');
        })->label('info');
        
        // Business Information (if applicable)
        $show->divider('Business Information');
        $show->field('business_name', __('Business Name'));
        $show->field('business_license_number', __('License Number'));
        $show->field('business_address', __('Business Address'));
        $show->field('business_phone_number', __('Business Phone'));
        $show->field('business_email', __('Business Email'));
        
        // System Information
        $show->divider('System Information');
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Administrator());

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        // Personal Information
        $form->divider('Personal Information');
        
        $form->text('name', __('Full Name'))
            ->rules('required');
        
        $form->text('username', __('Username'))
            ->creationRules(['required', 'unique:admin_users'])
            ->updateRules(['required', 'unique:admin_users,username,{{id}}']);
        
        $form->radio('gender', __('Gender'))
            ->options(['Male' => 'Male', 'Female' => 'Female'])
            ->default('Male');
        
        $form->text('nin', __('National ID Number'));
        
        $form->image('avatar', __('Profile Photo'));

        // Contact Information
        $form->divider('Contact Information');
        
        $form->text('phone_number', __('Phone Number'))
            ->creationRules(['required', 'unique:admin_users'])
            ->updateRules(['required', 'unique:admin_users,phone_number,{{id}}']);
        
        $form->text('phone_number_2', __('Alternative Phone'));
        
        $form->email('email', __('Email'))
            ->creationRules(['nullable', 'email', 'unique:admin_users'])
            ->updateRules(['nullable', 'email', 'unique:admin_users,email,{{id}}']);
        
        $form->textarea('address', __('Address'))->rows(3);

        // Account Settings & Permissions
        $form->divider('Account Settings & Permissions');
        
        $form->password('password', __('Password'))
            ->creationRules(['required', 'min:6'])
            ->updateRules(['nullable', 'min:6']);
        
        $form->radio('status', __('Account Status'))
            ->options([1 => 'Active', 0 => 'Inactive'])
            ->default(1);
        
        $form->multipleSelect('roles', __('Assigned Roles'))
            ->options(Role::all()->pluck('name', 'id'));
        
        $form->radio('dvo', __('District Veterinary Officer'))
            ->options([1 => 'Yes', 0 => 'No'])
            ->default(0);
        
        $form->radio('scvo', __('Sub County Veterinary Officer'))
            ->options([1 => 'Yes', 0 => 'No'])
            ->default(0);

        // Business Information (Optional)
        $form->divider('Business Information (Optional)');
        
        $form->text('business_name', __('Business Name'));
        
        $form->text('business_license_number', __('License Number'));
        
        $form->textarea('business_address', __('Business Address'))->rows(2);
        
        $form->text('business_phone_number', __('Business Phone'));
        
        $form->email('business_email', __('Business Email'));
        
        $form->radio('vet_service', __('Provides Vet Services'))
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->default('No');

        // Saving hooks
        $form->saving(function (Form $form) {
            // Split full name into first_name and last_name if they are empty
            if (!empty($form->name) && empty($form->first_name) && empty($form->last_name)) {
                $nameParts = explode(' ', trim($form->name), 2);
                $form->first_name = $nameParts[0] ?? '';
                $form->last_name = $nameParts[1] ?? '';
            }
            
            // Hash password if provided
            if ($form->password) {
                $form->password = Hash::make($form->password);
            } else {
                unset($form->password);
            }
            
            // Set defaults
            if (empty($form->request_status)) {
                $form->request_status = 'Approved';
            }
        });

        return $form;
    }
}
