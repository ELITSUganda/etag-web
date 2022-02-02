<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\District;
use App\Models\MovementHasMovementAnimal;
use App\Models\SubCounty;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MovementsItemsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Movements Items';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
 
 
   

        $grid = new Grid(new MovementHasMovementAnimal());
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->disableCreateButton();
        $grid->disableActions();
        


        $grid->column('id', __('Id'))->sortable();
        $grid->column('status', __('Status'))->display(function ($user) {
            return $this->movement->status ? $this->movement->status : "-";
        });
        $grid->column('pemit_no', __('Permit No.'))->display(function ($user) {
            return $this->movement->permit_Number ? $this->movement->permit_Number : "-";
        });
        $grid->column('valid_from_Date', __('Valid From'))->display(function ($user) {
            return $this->movement->valid_from_Date ? $this->movement->valid_from_Date : "-";
        });
        $grid->column('valid_to_Date', __('Valid To'))->display(function ($user) {
            return $this->movement->valid_to_Date ? $this->movement->valid_to_Date : "-";
        });
        $grid->column('created_at', __('Date'))->display(function ($user) {
            return $this->movement->created_at ? Carbon::parse($this->movement->created_at)->toFormattedDateString() : "-";
        });
        $grid->column('administrator_id', __('Name'))->display(function ($user) {
            if($this->movement->administrator_id == null){
                return "";
            }
            $_user = Administrator::find($this->movement->administrator_id);
            if (!$_user) {
                return "-";
            }
            return $_user->name; 
        });
        $grid->column('administrator_nin', __('ID No.'))->display(function ($user) {
            if($this->movement->administrator_id == null){
                return "";
            }
            $_user = Administrator::find($this->movement->administrator_id);
            if (!$_user) {
                return "-";
            }
            return $_user->nin; 
        });
        $grid->column('phone', __('Phone'))->display(function ($user) {
            if($this->movement->administrator_id == null){
                return "";
            }
            $_user = Administrator::find($this->movement->administrator_id);
            if (!$_user) {
                return "-";
            }
            return $_user->phone_number; 
        });
        $grid->column('district_from', __('District'))->display(function ($user) {
            if($this->movement->district_from == null){
                return "";
            }
            $_user = District::find($this->movement->district_from);
            if (!$_user) {
                return "-";
            }
            return $_user->name; 
        });
        $grid->column('sub_county_from', __('Sub-county'))->display(function ($user) {
            if($this->movement->sub_county_from == null){
                return "";
            }
            $_user = SubCounty::find($this->movement->sub_county_from);
            if (!$_user) {
                return "-";
            }
            return $_user->name; 
        });
        
        $grid->column('village_from', __('village'))->display(function ($user) {
            return $this->movement->village_from ? $this->movement->village_from : "-";
        });
        
        $grid->column('movement_animal_id', __('Animal'))->display(function ($animal_id) {
            if($animal_id == null){
                return "";
            }
            $_user = Animal::find($animal_id);
            if (!$_user) {
                return "-";
            }
            return $_user->e_id." - ".$_user->v_id; 
        });
        

        /*
         trader_nin
trader_name
trader_phone
transporter_reg
transporter_nin	
transporter_Phone
	


district_to
sub_county_to
village_to
transportation_route 
permit_Number 

         */
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
        $show = new Show(MovementHasMovementAnimal::findOrFail($id)); 

        $show->field('id', __('Id')); 
        
        return $show;
    }
 


}
