<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\MovementAnimal;
use App\Models\MovementHasMovementAnimal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\NestedForm;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Movement';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        if(isset($_GET['accept'])){
            $delete = (int)(trim($_GET['accept']));
            if($delete>0){
                $move = Movement::where('status','pending')->first();
                if($move != null){
                    foreach ($move->movement_has_movement_animals as $key => $ani) {
 
                        $e = new Event();
                        $e->administrator_id = $move->to_farm->administrator_id;
                        $e->district_id = $move->to_farm->district_id;
                        $e->sub_county_id = $move->to_farm->sub_county_id;
                        $e->parish_id = $move->to_farm->parish_id;
                        $e->farm_id = $move->to_farm->id;
                        $e->animal_id = $ani->movement_animal_id;
                        $e->type = "Moved";
                        $e->approved_by = $move->administrator_id;
                        $e->detail = "Animal moved from form  ".$move->to_farm->holding_code." TO farm ".$move->from_farm->holding_code;

                        $new_animal = Animal::find($ani->movement_animal_id)->first();
                        if($new_animal != null){ 
                            $new_animal->administrator_id = $move->to_farm->administrator_id;
                            $new_animal->district_id = $move->to_farm->district_id;
                            $new_animal->sub_county_id = $move->to_farm->sub_county_id;
                            $new_animal->parish_id = $move->to_farm->parish_id;
                            $new_animal->farm_id = $move->to_farm->id;

                            $new_animal->save();
                            
                        }
                    } 

                    $move->status = "Completed";
                    $e->save();
                    $move->save();
                }
            }  
            return redirect(admin_url('movements'));
        }
        
        if(isset($_GET['delete'])){
            $delete = (int)(trim($_GET['delete']));
            if($delete>0){
                $move = Movement::where('status','pending')->first();
                if($move != null){
                    $move->delete();
                }
 

            } 
            return redirect(admin_url('movements'));
        }
        $grid = new Grid(new Movement());
        //admin_toastr('Message...', 'success');


        $grid->header(function ($query) {
            $move = Movement::where('status','pending')->first();
            
            if($move!=null){
 
                $content  = '';
                if($move->to == $move->from){
                    $content = '<p>Invalid movement. You cannot move animals from one farm to same farm. You Either need to rectify this form or delete it.</p>';
                    $content .= '<p><a href="'.admin_url('movements/'.$move->id.'/edit').'" class="btn btn-success  ">Rectify form</a></p>';
                    $content .= '<p><a href="'.admin_url('movements?delete='.$move->id.'').'" class="btn btn-danger  ">Delete form</a></p>';
                }else{
                    if($move->movement_has_movement_animals!=null){
                        $found = [];
                        $_found = [];
                        foreach ($move->movement_has_movement_animals as $key => $m) {
                            if(in_array($m->movement_animal_id,$found)){
                                $m->delete();
                            }else{
                                $found[] = $m->movement_animal_id;
                                $_found[] = $m;
                            }
                        }
                    }
                    
                    $content .= "Following animals are going to be transfered from farm <b>".
                    $move->from_farm->holding_code . " - By " . $move->from_farm->owner()->name."</b> TO farm <b>".
                    $move->to_farm->holding_code . " - By " . $move->to_farm->owner()->name."</b>";
                    $content .= "<h4><u>Animals</u></h4><ol>";
                    foreach ($_found as $key => $animal) {
                        $content .= '<li> TAG ID: '.$animal->animal->v_id.', E-ID: '.$animal->animal->e_id.'</li>';
                    }
                    $content .= "</ol>";

                    $content .= '<p><a href="'.admin_url('movements/'.$move->id.'/edit').'" class="btn btn-primary  ">EDIT MOVEMENT</a></p>';
                    $content .= '<p><a href="'.admin_url('movements?delete='.$move->id.'').'" class="btn btn-danger  ">CACNEL MOVEMENT</a></p>';
                    $content .= '<p><a href="'.admin_url('movements?accept='.$move->id.'').'" class="btn btn-success  ">CONFIRM MOVEMENT</a></p>';

                }

                if($content != ""){
                    $box = new Box('Confirm pending movement - form #'.$move->id, $content);
                    $box->style('danger');
                    $box->solid();
                    return $box;
                }
            }


        });  

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('from', __('From'));
        $grid->column('to', __('To'));
        $grid->column('vehicle', __('Vehicle'));
        $grid->column('reason', __('Reason'));

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
        $show = new Show(Movement::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('from', __('From'));
        $show->field('to', __('To'));
        $show->field('vehicle', __('Vehicle'));
        $show->field('reason', __('Reason'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Movement());

        $items = [];
        foreach (Farm::all() as $key => $f) {
            $items[$f->id] = $f->holding_code . " - By " . $f->owner()->name;
        }


        $form->select('from', __('From farm'))
            ->options($items)
            ->required();

        $form->select('to', __('To farm'))
            ->options($items)
            ->required();

        $form->text('vehicle', __('Vehicle number plate'))->required();
        $form->text('reason', __('Reason'))->required();
        $user = Auth::user();
        $form->select('administrator_id', __('Approved by'))
            ->options(array(
                $user->id => $user->name
            ))
            ->default($user->id)
            ->value($user->id)
            ->readonly()
            ->required();


        $form->html('<h3>Click on "New" to Add animals to move in this list.</h3>');

        $form->hasMany('movement_has_movement_animals', null, function (NestedForm $form) {
            $_items = [];
            foreach (Animal::all() as $key => $item) {
                $_items[$item->id] = $item->e_id . " - " . $item->v_id;
            }
            $form->select('movement_animal_id', 'Select animal')->options($_items)
                ->required();
        });


        return $form;
    }
}
