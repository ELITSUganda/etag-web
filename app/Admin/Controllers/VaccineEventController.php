<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\Disease;
use App\Models\District;
use App\Models\DistrictVaccineStock;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Medicine;
use App\Models\SubCounty;
use App\Models\Utils;
use App\Models\Vaccine;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Faker\Factory as Faker;

class VaccineEventController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Vaccine Events';

    /**
     * Make a grid builder.
     

     * @return Grid
     */
    protected function grid()
    {
        /* $m = Event::find(34514);
        $m->detail .= '1';
        $m->save(); */

        /*         $data = Event::where([
            'type' => 'Milking'
        ])->whereBetween('created_at',['2023-05-11','2023-05-12'])->get();


        $ans = [];
        foreach ($data as $key => $d) {
            if(in_array($d->animal_id,$ans)){
                $d->delete();
                continue;
            }
            $ans[] = $d->animal_id;
        }
        dd($ans); */


        //Utils::display_alert_message();
        $grid = new Grid(new Event());

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        /*$faker = \Faker\Factory::create();
        
        $types = Array(
            'Sick' => 'Sick',
            'Healed' => 'Healed',
            'Vaccinated' => 'Vaccinated',
            'Gave birth' => 'Gave birth',
            'Sold' => 'Sold',
            'Died' => 'Died',
            'Slautered' => 'Slautered',
            'Stolen' => 'Stolen',
        );
        Event::truncate();
        for($i = 0;$i<300;$i++){
            $e = new Event();
            shuffle($types); 
            $e->type = $types[0];
            $e->detail = $faker->sentence();
            $e->approved_by = 1;
            $e->animal_id = rand(1,400);
            $e->save();
        }
        dd("Done");*/
        /*  $e = new Event();
        $e->weight = 130;
        $e->type = 'Weight check';
        $e->animal_id = 16186;

        $e->save(); */


        if (Admin::user()->isRole('farmer')) {
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
            $grid->actions(function ($actions) {
                //$actions->disableDelete();
                $actions->disableEdit();
            });
            //$grid->disableCreateButton();
        }


        $grid->model()->orderBy('id', 'DESC');
        $grid->filter(function ($filter) {



            $admins = [];
            foreach (Administrator::all() as $key => $v) {
                if (!$v->isRole('farmer')) {
                    continue;
                }
                $admins[$v->id] = $v->name . " - " . $v->id;
            }

            $animals = [];
            foreach (Animal::all() as $key => $v) {
                $animals[$v->id] = $v->e_id . " - " . $v->v_id;
            }

            $filter->equal('administrator_id', "Owner")->select($admins);

            $filter->equal('type', "Animal type")->select(array(
                'Cattle' => "Cattle",
                'Goat' => "Goat",
                'Sheep' => "Sheep"
            ));

            $filter->equal('district_id', 'Filter by district')->select(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })
                ->ajax(
                    url('/api/ajax?'
                        . "&search_by_1=name"
                        . "&search_by_2=id"
                        . "&query_parent=0"
                        . "&model=Location")
                );

            $filter->equal('sub_county_id', 'Filter by sub-county')->select(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })
                ->ajax(
                    url('/api/sub-counties')
                );

            $filter->like('animal_id', "Animal")->select($animals);
            $filter->equal('type', "Event type")->select(array(
                'Milking' => 'Milking',
                'Disease test' => 'Disease',
                'Drug' => 'Teatment',
                'Vaccination' => 'Vaccination',
                'Pregnancy' => 'Pregnancy test',
                'Death' => 'Death',
                'Slaughter' => 'Slaughter',
                'Other' => 'Other',

            ));
            $filter->equal('disease_id', "Event type")->select(
                Disease::all()->pluck('name', 'name')
            );
            $filter->equal('medicine_id', "Drug")->select(
                Medicine::all()->pluck('name', 'name')
            );
            $filter->equal('vaccine_id', "Vaccine")->select(
                Vaccine::all()->pluck('name', 'name')
            );

            $filter->between('created_at', 'Created between')->date();
        });


        $grid->column('id', __('ID'))->sortable();


        /*         $grid->column('animal_id', __('E-ID'))
            ->display(function ($id) {
                $u = Animal::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->e_id;
            })->sortable(); */


        $grid->column('animal_id', __('V-ID'))
            ->display(function ($id) {
                $u = Animal::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->v_id;
            })->sortable();


        $grid->column('created_at', __('Date'))
            ->display(function ($f) {
                return Utils::my_date_time($f);
            })->sortable();
        $grid->column('type', __('Event Type'))->sortable();
        $grid->column('milk', __('Milk (Ltrs)'))->sortable();
        $grid->column('vaccine_id', __('Vaccine'))
            ->hide()
            ->display(function ($id) {
                $u = Vaccine::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();

        $grid->column('disease_id', __('Disease'))
            ->display(function ($id) {
                $u = Disease::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })
            ->hide()
            ->sortable();

        $grid->column('medicine_id', __('Drug'))
            ->hide()
            ->display(function ($id) {
                $u = Medicine::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();


        $grid->column('animal_type', __('Livestock species'))->sortable();

        $grid->column('detail', __('Details'))->sortable();
        $grid->column('description', __('Description'))->sortable();



        $grid->column('district_id', __('District'))
            ->display(function ($id) {
                return Utils::get_object(Location::class, $id)->name_text;
            })->sortable();
        $grid->column('sub_county_id', __('Sub county'))
            ->display(function ($id) {
                return Utils::get_object(Location::class, $id)->name_text;
            })->sortable();


        $grid->column('administrator_id', __('Animal owner'))
            ->display(function ($id) {
                $u = Administrator::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
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
        $show = new Show(Event::findOrFail($id));

        $show = new Show(Farm::findOrFail($id));
        if (Admin::user()->isRole('farmer')) {
            $show->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableDelete();
                });
        }


        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('farm_id', __('Farm id'));
        $show->field('animal_id', __('Animal id'));
        $show->field('type', __('Type'));
        $show->field('approved_by', __('Approved by'));
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


        /*
        die("sone"); */


        /*    $ids = [];
        foreach (Animal::where([  
            'administrator_id' => Auth::user()->id
        ])->get() as $k => $a) {
            $ids[] = $a->id;
        }

        $faker = Faker::create();
        for ($x = 1; $x < 25; $x++) {
            $e = new Event();
            $e->animal_id = $ids[rand(1,3)];
            $e->created_at = $faker->dateTimeBetween('-1 year');
            $e->type = "Milking";
            $e->milk = rand(1, 15);
           try {
            $e->save();
           } catch (\Throwable $th) {
            //throw $th;
           }
            echo $x."<br>";
        }  
        dd("done");
 */
        Utils::display_alert_message();
        $form = new Form(new Event());

        $u = Admin::user();
        $form->hidden('administrator_id', __('Administrator id'))->default($u->id);
        $form->hidden('district_id', __('District id'))->default(1);
        $form->hidden('sub_county_id', __('Sub county id'))->default(1);
        $form->hidden('is_batch_import', __('Sub county id'))->default(0);
        $form->hidden('farm_id', __('Farm id'))->default(1);




        $form->select('animal_id', 'Select Animal')
            ->options(function ($id) {
                $parent = Animal::find($id);
                if ($parent != null) {
                    return [$parent->id =>  $parent->v_id . " - " . $parent->e_id];
                }
            })
            ->rules('required')
            ->ajax(
                url('/api/ajax-animals?'
                    . "&administrator_id={$u->id}")
            )->rules('required');

        $form->divider();
        $form->radio('type', __('Event type'))
            ->options(array(
                'Vaccination' => 'Vaccination',
            ))
            ->when('Vaccination', function (Form $form) {


                foreach (DistrictVaccineStock::all() as $stock) {
                    if ($stock->current_quantity < 1) {
                        continue;
                    }
                    $stocks[$stock->id] = "$stock->id. " . $stock->drug_category->name_of_drug . " - Batch #" .
                        $stock->batch_number . ", Available Quantity: " . $stock->current_quantity_text;
                }


                $form->select('vaccine_id', __('Please select Vaccine'))
                    ->options($stocks)
                    ->rules('required');
                //vaccine quantity in vaccination
                $form->decimal('vaccination', __('Vaccine quantity (in Milliliters)'))
                    ->rules('required|numeric|min:1');
            });
        $form->divider();
        $form->text('detail', __('Event Details'));

        $user = Auth::user();
        $form->hidden('approved_by', __('Approved by'))
            ->default($user->id)
            ->value($user->id)
            ->readonly()
            ->required();


        $form->disableEditingCheck();
        $form->disableViewCheck();
        $form->disableReset();

        return $form;
    }
}
